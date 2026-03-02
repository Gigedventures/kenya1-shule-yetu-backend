<?php

namespace Tests\Feature;

use App\Modules\ShuleYetu\Finance\Services\FeeService;
use App\Modules\ShuleYetu\Models\ShuleAcademicYear;
use App\Modules\ShuleYetu\Models\ShuleClass;
use App\Modules\ShuleYetu\Models\ShuleFeeItem;
use App\Modules\ShuleYetu\Models\ShuleFeeStructure;
use App\Modules\ShuleYetu\Models\ShuleSchool;
use App\Modules\ShuleYetu\Models\ShuleStudent;
use App\Modules\ShuleYetu\Models\ShuleStudentBill;
use App\Modules\ShuleYetu\Models\ShuleStudentEnrollment;
use App\Modules\ShuleYetu\Models\ShuleTerm;
use App\Modules\ShuleYetu\Support\Tenancy\SchoolContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

class LedgerDoubleEntryTest extends TestCase
{
    use RefreshDatabase;

    public function test_double_entry_ledger_for_bill_payment_and_reversal(): void
    {
        $school = ShuleSchool::query()->create([
            'name' => 'School L2',
            'code' => 'FN-L2-' . Str::upper(Str::random(5)),
            'status' => 'active',
        ]);
        app(SchoolContext::class)->setId($school->id);

        $year = ShuleAcademicYear::query()->create([
            'name' => '2026',
            'start_date' => now()->startOfYear(),
            'end_date' => now()->endOfYear(),
            'is_active' => true,
        ]);
        $term = ShuleTerm::query()->create([
            'academic_year_id' => $year->id,
            'name' => 'Term 1',
            'start_date' => now()->startOfYear(),
            'end_date' => now()->startOfYear()->addMonths(3),
        ]);
        $class = ShuleClass::query()->create([
            'name' => 'Grade 7',
            'level' => 7,
        ]);
        $structure = ShuleFeeStructure::query()->create([
            'school_id' => $school->id,
            'academic_year_id' => $year->id,
            'term_id' => $term->id,
            'class_id' => $class->id,
            'name' => 'Fees',
            'is_active' => true,
        ]);
        ShuleFeeItem::query()->create([
            'fee_structure_id' => $structure->id,
            'name' => 'Tuition',
            'amount' => 1000,
            'is_mandatory' => true,
        ]);

        $student = ShuleStudent::query()->create([
            'first_name' => 'Ledger',
            'last_name' => 'Bridge',
            'current_class_id' => $class->id,
            'status' => 'active',
        ]);
        ShuleStudentEnrollment::query()->create([
            'student_id' => $student->id,
            'academic_year_id' => $year->id,
            'class_id' => $class->id,
            'status' => 'enrolled',
        ]);

        $service = app(FeeService::class);
        $service->generateBillsForStructure($structure->id, $school->id);
        $bill = ShuleStudentBill::query()
            ->where('school_id', $school->id)
            ->where('student_id', $student->id)
            ->firstOrFail();

        // Bill event: AR debit + revenue credit
        $this->assertDatabaseHas('k1_ledger_entries', [
            'tenant_id' => $school->id,
            'reference_type' => 'bill',
            'reference_id' => $bill->id,
            'debit' => 1000.00,
        ]);

        $user = $this->fakeUserWithPermission('finance.payments.record');
        $payment = $service->recordPayment($student->id, 600, 'cash', null, $user);

        // Payment event: AR credit + cash debit
        $this->assertDatabaseHas('k1_ledger_entries', [
            'tenant_id' => $school->id,
            'reference_type' => 'payment',
            'reference_id' => $payment->id,
            'credit' => 600.00,
        ]);
        $this->assertDatabaseHas('k1_ledger_entries', [
            'tenant_id' => $school->id,
            'reference_type' => 'payment',
            'reference_id' => $payment->id,
            'debit' => 600.00,
        ]);

        $reversal = $service->reversePayment($payment->id, 'posted by mistake', $user);

        // Reversal event must compensate payment lines.
        $paymentLines = DB::table('k1_ledger_entries as le')
            ->join('k1_accounts as ka', 'ka.id', '=', 'le.account_id')
            ->where('le.tenant_id', $school->id)
            ->where('reference_type', 'payment')
            ->where('reference_id', $payment->id)
            ->orderBy('ka.code')
            ->get(['le.debit', 'le.credit', 'ka.code as account_code']);
        $reversalLines = DB::table('k1_ledger_entries as le')
            ->join('k1_accounts as ka', 'ka.id', '=', 'le.account_id')
            ->where('le.tenant_id', $school->id)
            ->where('reference_type', 'reversal')
            ->where('reference_id', $reversal->id)
            ->orderBy('ka.code')
            ->get(['le.debit', 'le.credit', 'ka.code as account_code']);

        $this->assertCount($paymentLines->count(), $reversalLines);
        foreach ($paymentLines as $index => $paymentLine) {
            $reversalLine = $reversalLines[$index];
            $this->assertSame($paymentLine->account_code, $reversalLine->account_code);
            $this->assertSame(
                round((float) $paymentLine->debit, 2),
                round((float) $reversalLine->credit, 2)
            );
            $this->assertSame(
                round((float) $paymentLine->credit, 2),
                round((float) $reversalLine->debit, 2)
            );
        }

        // Per-school ledger must remain balanced.
        $debits = (float) DB::table('k1_ledger_entries')
            ->where('tenant_id', $school->id)
            ->sum('debit');
        $credits = (float) DB::table('k1_ledger_entries')
            ->where('tenant_id', $school->id)
            ->sum('credit');
        $this->assertSame(round($debits, 2), round($credits, 2));
    }

    private function fakeUserWithPermission(string $permission): \App\Models\User
    {
        $user = \App\Models\User::query()->create([
            'name' => 'Tester',
            'email' => 'tester+' . Str::random(5) . '@example.com',
            'password' => bcrypt('secret'),
            'is_system_admin' => false,
        ]);

        $user->givePermissionTo($permission);

        return $user;
    }
}
