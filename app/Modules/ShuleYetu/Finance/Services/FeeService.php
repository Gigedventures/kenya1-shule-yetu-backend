<?php

namespace App\Modules\ShuleYetu\Finance\Services;

use App\Core\Finance\LedgerService;
use App\Models\User;
use App\Modules\ShuleYetu\Models\ShuleFeeItem;
use App\Modules\ShuleYetu\Models\ShuleFeeStructure;
use App\Modules\ShuleYetu\Models\ShuleExpense;
use App\Modules\ShuleYetu\Models\ShuleExpenseCategory;
use App\Modules\ShuleYetu\Models\ShuleReceipt;
use App\Modules\ShuleYetu\Models\ShulePayment;
use App\Modules\ShuleYetu\Models\ShulePaymentAllocation;
use App\Modules\ShuleYetu\Models\ShulePaymentReversal;
use App\Modules\ShuleYetu\Models\ShuleStudentBill;
use App\Modules\ShuleYetu\Support\Tenancy\SchoolContext;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\QueryException;
use Illuminate\Support\Str;
use RuntimeException;

class FeeService
{
    private const ACCOUNT_AR = '1100-AR';
    private const ACCOUNT_CASH = '1000-CASH';
    private const ACCOUNT_BILLING_REVENUE = '4000-BILLING-REVENUE';

    public function __construct(private readonly LedgerService $ledger)
    {
    }

    public function generateBillsForStructure(string $structureId, string $schoolId): int
    {
        if (!$schoolId) {
            throw new RuntimeException('school_id is required.');
        }

        $structure = ShuleFeeStructure::query()
            ->where('school_id', $schoolId)
            ->findOrFail($structureId);

        $totalAmount = (float) ShuleFeeItem::query()
            ->where('fee_structure_id', $structure->id)
            ->sum('amount');

        if ($totalAmount <= 0) {
            throw new RuntimeException('Fee structure has no items.');
        }

        $students = DB::table('shule_student_enrollments as se')
            ->join('shule_students as ss', 'ss.id', '=', 'se.student_id')
            ->where('se.school_id', $schoolId)
            ->where('se.academic_year_id', $structure->academic_year_id)
            ->where('se.class_id', $structure->class_id)
            ->whereIn('se.status', ['enrolled', 'promoted', 'repeated'])
            ->where('ss.status', 'active')
            ->select(['ss.id'])
            ->get();

        return DB::transaction(function () use ($students, $structure, $totalAmount, $schoolId): int {
            $created = 0;
            foreach ($students as $student) {
                $bill = ShuleStudentBill::query()->updateOrCreate(
                    [
                        'school_id' => $schoolId,
                        'student_id' => $student->id,
                        'fee_structure_id' => $structure->id,
                    ],
                    [
                        'total_amount' => $totalAmount,
                        'paid_amount' => 0,
                        'balance' => $totalAmount,
                        'status' => 'unpaid',
                    ]
                );

                if ($bill->wasRecentlyCreated) {
                    $bill->invoice_number = $this->nextInvoiceNumber($schoolId);
                    $bill->issued_at = now();
                    $bill->due_date = null;
                    $bill->invoice_status = 'issued';
                    $bill->save();

                    $this->ledger->postEvent(
                        $schoolId,
                        'bill',
                        (string) $bill->id,
                        sprintf('Bill created for student %s', (string) $student->id),
                        [
                            [
                                'account_code' => self::ACCOUNT_AR,
                                'debit' => $totalAmount,
                                'credit' => null,
                            ],
                            [
                                'account_code' => self::ACCOUNT_BILLING_REVENUE,
                                'debit' => null,
                                'credit' => $totalAmount,
                            ],
                        ]
                    );
                }

                $created++;
            }

            return $created;
        });
    }

    public function recordPayment(
        string $studentId,
        float $amount,
        string $method,
        ?string $reference,
        User $actorUser,
        ?string $idempotencyKey = null
    ): ShulePayment {
        $schoolId = app(SchoolContext::class)->requireId();

        $studentSchoolId = DB::table('shule_students')
            ->where('id', $studentId)
            ->value('school_id');
        if (!$studentSchoolId || $studentSchoolId !== $schoolId) {
            throw new RuntimeException('Payment student must belong to active school.');
        }

        if ($amount <= 0) {
            throw new RuntimeException('Payment amount must be greater than zero.');
        }

        $idempotencyKey = $idempotencyKey !== null ? trim($idempotencyKey) : null;
        if ($idempotencyKey === '') {
            $idempotencyKey = null;
        }

        try {
            return DB::transaction(function () use (
                $schoolId,
                $studentId,
                $amount,
                $method,
                $reference,
                $actorUser,
                $idempotencyKey
            ): ShulePayment {
                if ($idempotencyKey) {
                    $existing = ShulePayment::query()
                        ->where('school_id', $schoolId)
                        ->where('idempotency_key', $idempotencyKey)
                        ->lockForUpdate()
                        ->first();

                    if ($existing) {
                        return $existing->load('allocations');
                    }
                }

                // Lock candidate bills in deterministic oldest-first order before any allocation write.
                $bills = ShuleStudentBill::query()
                    ->where('school_id', $schoolId)
                    ->where('student_id', $studentId)
                    ->where('balance', '>', 0)
                    ->orderBy('created_at', 'asc')
                    ->lockForUpdate()
                    ->get()
                    ->sortBy('created_at')
                    ->values();

                if ($bills->isEmpty()) {
                    throw new RuntimeException('No outstanding bills to allocate payment.');
                }

                $payment = ShulePayment::query()->create([
                    'student_id' => $studentId,
                    'amount' => $amount,
                    'status' => 'posted',
                    'payment_method' => $method,
                    'reference' => $reference,
                    'idempotency_key' => $idempotencyKey,
                    'received_by_user_id' => $actorUser->getKey(),
                    'payment_date' => now(),
                ]);

                $remaining = $amount;
                $allocationBaseTime = now();
                $allocationOrder = 0;

                foreach ($bills as $bill) {
                    if ($remaining <= 0) {
                        break;
                    }

                    // Re-read locked value at allocation time and never exceed current balance.
                    $currentBalance = (float) $bill->balance;
                    if ($currentBalance <= 0) {
                        continue;
                    }

                    $allocate = min($remaining, $currentBalance);
                    $allocation = ShulePaymentAllocation::query()->create([
                        'payment_id' => $payment->id,
                        'student_bill_id' => $bill->id,
                        'allocated_amount' => $allocate,
                    ]);
                    $allocationTimestamp = $allocationBaseTime->copy()->addSeconds($allocationOrder);
                    ShulePaymentAllocation::query()
                        ->where('id', $allocation->id)
                        ->update([
                            'created_at' => $allocationTimestamp,
                            'updated_at' => $allocationTimestamp,
                        ]);
                    $allocationOrder++;

                    $bill->paid_amount = round((float) $bill->paid_amount + $allocate, 2);
                    $bill->balance = round(max(0, (float) $bill->balance - $allocate), 2);
                    if ((float) $bill->balance <= 0) {
                        $bill->status = 'paid';
                    } elseif ((float) $bill->paid_amount > 0) {
                        $bill->status = 'partial';
                    } else {
                        $bill->status = 'unpaid';
                    }
                    $bill->save();

                    $remaining -= $allocate;
                }

                if ($remaining > 0) {
                    throw new RuntimeException('Payment exceeds outstanding balance.');
                }

                $allocatedTotal = (float) ShulePaymentAllocation::query()
                    ->where('payment_id', $payment->id)
                    ->sum('allocated_amount');
                if (round($allocatedTotal, 2) !== round($amount, 2)) {
                    throw new RuntimeException('Payment allocation invariant failed.');
                }

                $this->ledger->postEvent(
                    $schoolId,
                    'payment',
                    (string) $payment->id,
                    sprintf('Payment received for student %s', $studentId),
                    [
                        [
                            'account_code' => self::ACCOUNT_CASH,
                            'debit' => $amount,
                            'credit' => null,
                        ],
                        [
                            'account_code' => self::ACCOUNT_AR,
                            'debit' => null,
                            'credit' => $amount,
                        ],
                    ]
                );

                ShuleReceipt::query()->create([
                    'payment_id' => $payment->id,
                    'receipt_number' => $this->nextReceiptNumber($schoolId),
                    'issued_at' => now(),
                    'issued_by' => $actorUser->getKey(),
                ]);

                return $payment->load('allocations');
            });
        } catch (QueryException $e) {
            if ($idempotencyKey) {
                $existing = ShulePayment::query()
                    ->where('school_id', $schoolId)
                    ->where('idempotency_key', $idempotencyKey)
                    ->first();
                if ($existing) {
                    return $existing->load('allocations');
                }
            }

            throw $e;
        }
    }

    public function reversePayment(string $paymentId, string $reason, User $actor): ShulePaymentReversal
    {
        $schoolId = app(SchoolContext::class)->requireId();
        $reason = trim($reason);
        if ($reason === '') {
            throw new RuntimeException('Reversal reason is required.');
        }

        return DB::transaction(function () use ($paymentId, $reason, $actor, $schoolId): ShulePaymentReversal {
            $payment = ShulePayment::query()
                ->where('school_id', $schoolId)
                ->where('id', $paymentId)
                ->lockForUpdate()
                ->firstOrFail();

            if ($payment->status === 'reversed') {
                throw new RuntimeException('Payment is already reversed.');
            }

            $this->guardDownstreamReconciliations($payment->id);

            $allocations = ShulePaymentAllocation::query()
                ->where('school_id', $schoolId)
                ->where('payment_id', $payment->id)
                ->lockForUpdate()
                ->get();

            if ($allocations->isEmpty()) {
                throw new RuntimeException('Payment has no allocations to reverse.');
            }

            foreach ($allocations as $allocation) {
                $bill = ShuleStudentBill::query()
                    ->where('school_id', $schoolId)
                    ->where('id', $allocation->student_bill_id)
                    ->lockForUpdate()
                    ->firstOrFail();

                $allocatedAmount = (float) $allocation->allocated_amount;
                $bill->paid_amount = round(max(0, (float) $bill->paid_amount - $allocatedAmount), 2);
                $bill->balance = round(min((float) $bill->total_amount, (float) $bill->balance + $allocatedAmount), 2);

                $balance = (float) $bill->balance;
                $totalAmount = (float) $bill->total_amount;
                if (abs($balance - $totalAmount) < 0.00001) {
                    $bill->status = 'unpaid';
                } elseif (abs($balance) < 0.00001) {
                    $bill->status = 'paid';
                } else {
                    $bill->status = 'partial';
                }

                $bill->save();
            }

            $reversal = ShulePaymentReversal::query()->create([
                'payment_id' => $payment->id,
                'school_id' => $schoolId,
                'reversed_by' => $actor->getKey(),
                'reason' => $reason,
                'reversed_at' => now(),
            ]);

            $payment->status = 'reversed';
            $payment->save();

            $this->ledger->reverseEvent(
                $schoolId,
                'payment',
                (string) $payment->id,
                'reversal',
                (string) $reversal->id,
                sprintf('Reversal of payment %s: %s', (string) $payment->id, $reason)
            );

            return $reversal;
        });
    }

    public function generateStudentStatement(string $studentId): array
    {
        $schoolId = app(SchoolContext::class)->requireId();

        $student = DB::table('shule_students')
            ->where('id', $studentId)
            ->where('school_id', $schoolId)
            ->first(['id']);

        if (!$student) {
            throw new RuntimeException('Student must belong to active school.');
        }

        $entries = DB::table('k1_ledger_entries as le')
            ->join('k1_accounts as ka', 'ka.id', '=', 'le.account_id')
            ->where('le.tenant_id', $schoolId)
            ->where(function ($q) use ($studentId) {
                $q->whereExists(function ($sq) use ($studentId) {
                    $sq->select(DB::raw(1))
                        ->from('shule_student_bills as sb')
                        ->whereColumn('sb.id', 'le.reference_id')
                        ->where('le.reference_type', 'bill')
                        ->where('sb.student_id', $studentId);
                })->orWhereExists(function ($sq) use ($studentId) {
                    $sq->select(DB::raw(1))
                        ->from('shule_payments as sp')
                        ->whereColumn('sp.id', 'le.reference_id')
                        ->where('le.reference_type', 'payment')
                        ->where('sp.student_id', $studentId);
                })->orWhereExists(function ($sq) use ($studentId) {
                    $sq->select(DB::raw(1))
                        ->from('shule_payment_reversals as spr')
                        ->join('shule_payments as sp', 'sp.id', '=', 'spr.payment_id')
                        ->whereColumn('spr.id', 'le.reference_id')
                        ->where('le.reference_type', 'reversal')
                        ->where('sp.student_id', $studentId);
                });
            })
            ->where('ka.code', self::ACCOUNT_AR)
            ->orderBy('le.created_at')
            ->orderBy('le.id')
            ->get(['le.*']);

        $billed = (float) $entries->where('reference_type', 'bill')->sum('debit');
        $paid = (float) $entries->where('reference_type', 'payment')->sum('credit');
        $reversed = (float) $entries->where('reference_type', 'reversal')->sum('debit');
        $netPaid = $paid - $reversed;
        $outstanding = $billed - $netPaid;

        return [
            'student_id' => $studentId,
            'entries' => $entries,
            'summary' => [
                'total_billed' => round($billed, 2),
                'total_paid' => round($paid, 2),
                'total_reversed' => round($reversed, 2),
                'net_paid' => round($netPaid, 2),
                'outstanding' => round($outstanding, 2),
            ],
        ];
    }

    public function getSchoolRevenueSummaryReport(): array
    {
        $schoolId = app(SchoolContext::class)->requireId();

        $totals = DB::table('k1_ledger_entries')
            ->where('tenant_id', $schoolId)
            ->selectRaw("
                COALESCE(SUM(CASE WHEN reference_type = 'bill' THEN debit ELSE 0 END), 0) as billed,
                COALESCE(SUM(CASE WHEN reference_type = 'payment' THEN credit ELSE 0 END), 0) as collected,
                COALESCE(SUM(CASE WHEN reference_type = 'reversal' THEN debit ELSE 0 END), 0) as reversed
            ")
            ->first();

        $totalBilled = (float) ($totals->billed ?? 0);
        $totalCollected = (float) ($totals->collected ?? 0);
        $totalReversed = (float) ($totals->reversed ?? 0);
        $netRevenue = $totalCollected - $totalReversed;

        return [
            'total_billed' => round($totalBilled, 2),
            'total_collected' => round($totalCollected, 2),
            'total_reversed' => round($totalReversed, 2),
            'net_revenue' => round($netRevenue, 2),
        ];
    }

    public function getOutstandingBalancesReport(): array
    {
        $schoolId = app(SchoolContext::class)->requireId();

        $totalOutstanding = (float) ShuleStudentBill::query()
            ->where('school_id', $schoolId)
            ->whereIn('status', ['unpaid', 'partial'])
            ->sum('balance');

        $students = DB::table('shule_student_bills as sb')
            ->join('shule_students as ss', 'ss.id', '=', 'sb.student_id')
            ->where('sb.school_id', $schoolId)
            ->whereIn('sb.status', ['unpaid', 'partial'])
            ->groupBy('sb.student_id', 'ss.first_name', 'ss.last_name')
            ->selectRaw('
                sb.student_id,
                ss.first_name,
                ss.last_name,
                SUM(sb.balance) as outstanding_balance
            ')
            ->orderByDesc('outstanding_balance')
            ->get();

        return [
            'total_outstanding' => round($totalOutstanding, 2),
            'students' => $students,
        ];
    }

    public function getRevenueByTermReport(): array
    {
        $schoolId = app(SchoolContext::class)->requireId();

        $terms = DB::table('shule_payment_allocations as spa')
            ->join('shule_payments as sp', 'sp.id', '=', 'spa.payment_id')
            ->join('shule_student_bills as sb', 'sb.id', '=', 'spa.student_bill_id')
            ->join('shule_fee_structures as sfs', 'sfs.id', '=', 'sb.fee_structure_id')
            ->join('shule_terms as st', 'st.id', '=', 'sfs.term_id')
            ->where('spa.school_id', $schoolId)
            ->where('sp.school_id', $schoolId)
            ->where('sp.status', 'posted')
            ->groupBy('st.id', 'st.name')
            ->selectRaw('st.id as term_id, st.name as term_name, SUM(spa.allocated_amount) as revenue_collected')
            ->orderBy('st.name')
            ->get();

        return [
            'terms' => $terms,
        ];
    }

    public function postExpense(string $expenseId, User $actor): ShuleExpense
    {
        $schoolId = app(SchoolContext::class)->requireId();

        return DB::transaction(function () use ($expenseId, $actor, $schoolId): ShuleExpense {
            $expense = ShuleExpense::query()
                ->with('category')
                ->where('school_id', $schoolId)
                ->where('id', $expenseId)
                ->lockForUpdate()
                ->firstOrFail();

            if ($expense->status === 'posted') {
                throw new RuntimeException('Expense is already posted.');
            }

            $accountCode = $expense->category?->expense_account_code ?: '5000-EXPENSE';
            $amount = (float) $expense->amount;

            $this->ledger->postEvent(
                $schoolId,
                'expense',
                (string) $expense->id,
                sprintf('Expense posted: %s', (string) $expense->description),
                [
                    [
                        'account_code' => $accountCode,
                        'debit' => $amount,
                        'credit' => null,
                    ],
                    [
                        'account_code' => self::ACCOUNT_CASH,
                        'debit' => null,
                        'credit' => $amount,
                    ],
                ]
            );

            $expense->status = 'posted';
            $expense->approved_by = $actor->getKey();
            $expense->save();

            return $expense;
        });
    }

    public function getRevenueSummary(?string $termId = null, ?string $dateFrom = null, ?string $dateTo = null): array
    {
        $schoolId = app(SchoolContext::class)->requireId();

        $query = DB::table('shule_payment_allocations as spa')
            ->join('shule_payments as sp', 'sp.id', '=', 'spa.payment_id')
            ->join('shule_student_bills as sb', 'sb.id', '=', 'spa.student_bill_id')
            ->join('shule_fee_structures as sfs', 'sfs.id', '=', 'sb.fee_structure_id')
            ->join('shule_terms as st', 'st.id', '=', 'sfs.term_id')
            ->where('spa.school_id', $schoolId)
            ->where('sp.school_id', $schoolId)
            ->where('sp.status', 'posted');

        if ($termId) {
            $query->where('st.id', $termId);
        }
        if ($dateFrom) {
            $query->whereDate('sp.payment_date', '>=', $dateFrom);
        }
        if ($dateTo) {
            $query->whereDate('sp.payment_date', '<=', $dateTo);
        }

        $total = (float) $query->sum('spa.allocated_amount');

        $byTerm = $query->clone()
            ->groupBy('st.id', 'st.name')
            ->selectRaw('st.id as term_id, st.name as term_name, SUM(spa.allocated_amount) as total')
            ->orderBy('st.name')
            ->get();

        return [
            'total_revenue' => round($total, 2),
            'by_term' => $byTerm,
        ];
    }

    public function getAccountsReceivableAging(?string $asOfDate = null): array
    {
        $schoolId = app(SchoolContext::class)->requireId();
        $asOf = $asOfDate ? Carbon::parse($asOfDate)->toDateString() : now()->toDateString();

        $rows = ShuleStudentBill::query()
            ->where('school_id', $schoolId)
            ->where('balance', '>', 0)
            ->get(['id', 'student_id', 'balance', 'due_date', 'issued_at', 'created_at']);

        $buckets = [
            'current' => 0.0,
            '1_30' => 0.0,
            '31_60' => 0.0,
            '61_90' => 0.0,
            '90_plus' => 0.0,
        ];

        foreach ($rows as $row) {
            $basis = $row->due_date ?: ($row->issued_at?->toDateString() ?: $row->created_at->toDateString());
            $days = Carbon::parse($basis)->diffInDays(Carbon::parse($asOf), false);
            $amount = (float) $row->balance;

            if ($days <= 0) {
                $buckets['current'] += $amount;
            } elseif ($days <= 30) {
                $buckets['1_30'] += $amount;
            } elseif ($days <= 60) {
                $buckets['31_60'] += $amount;
            } elseif ($days <= 90) {
                $buckets['61_90'] += $amount;
            } else {
                $buckets['90_plus'] += $amount;
            }
        }

        return [
            'as_of_date' => $asOf,
            'total_receivable' => round(array_sum($buckets), 2),
            'aging' => array_map(fn ($v) => round($v, 2), $buckets),
        ];
    }

    public function getExpensesSummary(?string $dateFrom = null, ?string $dateTo = null): array
    {
        $schoolId = app(SchoolContext::class)->requireId();

        $query = DB::table('shule_expenses as se')
            ->join('shule_expense_categories as sec', 'sec.id', '=', 'se.category_id')
            ->where('se.school_id', $schoolId)
            ->where('se.status', 'posted');

        if ($dateFrom) {
            $query->whereDate('se.expense_date', '>=', $dateFrom);
        }
        if ($dateTo) {
            $query->whereDate('se.expense_date', '<=', $dateTo);
        }

        $total = (float) $query->sum('se.amount');
        $byCategory = $query->clone()
            ->groupBy('sec.id', 'sec.name')
            ->selectRaw('sec.id as category_id, sec.name as category_name, SUM(se.amount) as total')
            ->orderBy('sec.name')
            ->get();

        return [
            'total_expenses' => round($total, 2),
            'by_category' => $byCategory,
        ];
    }

    public function getTrialBalance(?string $dateFrom = null, ?string $dateTo = null): array
    {
        $schoolId = app(SchoolContext::class)->requireId();

        $query = DB::table('k1_ledger_entries as le')
            ->join('k1_accounts as ka', 'ka.id', '=', 'le.account_id')
            ->where('le.tenant_id', $schoolId);

        if ($dateFrom) {
            $query->whereDate('le.created_at', '>=', $dateFrom);
        }
        if ($dateTo) {
            $query->whereDate('le.created_at', '<=', $dateTo);
        }

        $lines = $query->groupBy('ka.code', 'ka.name')
            ->selectRaw('ka.code as account_code, ka.name as account_name, SUM(le.debit) as debit, SUM(le.credit) as credit')
            ->orderBy('ka.code')
            ->get();

        $totalDebit = (float) $lines->sum('debit');
        $totalCredit = (float) $lines->sum('credit');

        return [
            'lines' => $lines,
            'total_debit' => round($totalDebit, 2),
            'total_credit' => round($totalCredit, 2),
            'is_balanced' => round($totalDebit, 2) === round($totalCredit, 2),
        ];
    }

    public function getStudentStatement(string $studentId): array
    {
        $schoolId = app(SchoolContext::class)->requireId();

        $studentSchoolId = DB::table('shule_students')
            ->where('id', $studentId)
            ->value('school_id');
        if (!$studentSchoolId || $studentSchoolId !== $schoolId) {
            throw new RuntimeException('Student must belong to active school.');
        }

        $bills = ShuleStudentBill::query()
            ->with('feeStructure')
            ->where('student_id', $studentId)
            ->orderBy('created_at')
            ->get();

        $payments = ShulePayment::query()
            ->with('allocations')
            ->where('student_id', $studentId)
            ->orderBy('payment_date')
            ->get();

        $totalBilled = (float) $bills->sum('total_amount');
        $totalPaid = (float) $payments->sum('amount');
        $balance = $totalBilled - $totalPaid;

        return [
            'student_id' => $studentId,
            'bills' => $bills,
            'payments' => $payments,
            'summary' => [
                'total_billed' => round($totalBilled, 2),
                'total_paid' => round($totalPaid, 2),
                'balance' => round($balance, 2),
            ],
        ];
    }

    public function getFeeSummaryReport(string $termId): array
    {
        $schoolId = app(SchoolContext::class)->requireId();

        $termSchoolId = DB::table('shule_terms')
            ->where('id', $termId)
            ->value('school_id');
        if (!$termSchoolId || $termSchoolId !== $schoolId) {
            throw new RuntimeException('Term must belong to active school.');
        }

        $billed = (float) DB::table('shule_student_bills as sb')
            ->join('shule_fee_structures as sfs', 'sfs.id', '=', 'sb.fee_structure_id')
            ->where('sb.school_id', $schoolId)
            ->where('sfs.term_id', $termId)
            ->sum('sb.total_amount');

        $collected = (float) DB::table('shule_payment_allocations as spa')
            ->join('shule_student_bills as sb', 'sb.id', '=', 'spa.student_bill_id')
            ->join('shule_fee_structures as sfs', 'sfs.id', '=', 'sb.fee_structure_id')
            ->where('spa.school_id', $schoolId)
            ->where('sfs.term_id', $termId)
            ->sum('spa.allocated_amount');

        $outstanding = $billed - $collected;
        $percentage = $billed > 0 ? round(($collected / $billed) * 100, 2) : 0.0;

        return [
            'term_id' => $termId,
            'total_billed' => round($billed, 2),
            'total_collected' => round($collected, 2),
            'outstanding' => round($outstanding, 2),
            'collection_percentage' => $percentage,
        ];
    }

    private function guardDownstreamReconciliations(string $paymentId): void
    {
        $candidateTables = [
            'shule_payment_reconciliations',
            'shule_bank_reconciliations',
            'shule_reconciliations',
        ];

        foreach ($candidateTables as $table) {
            if (!Schema::hasTable($table) || !Schema::hasColumn($table, 'payment_id')) {
                continue;
            }

            if (DB::table($table)->where('payment_id', $paymentId)->exists()) {
                throw new RuntimeException('Cannot reverse payment with downstream reconciliations.');
            }
        }
    }

    private function nextInvoiceNumber(string $schoolId): string
    {
        return 'INV-' . now()->format('YmdHis') . '-' . strtoupper(substr(Str::uuid()->toString(), 0, 6));
    }

    private function nextReceiptNumber(string $schoolId): string
    {
        return 'RCT-' . now()->format('YmdHis') . '-' . strtoupper(substr(Str::uuid()->toString(), 0, 6));
    }
}
