<?php

namespace App\Modules\ShuleYetu\Transport\Services;

use App\Core\Finance\LedgerService;
use App\Models\User;
use App\Modules\ShuleYetu\Models\ShuleRoute;
use App\Modules\ShuleYetu\Models\ShuleTransportPayment;
use App\Modules\ShuleYetu\Support\Tenancy\SchoolContext;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class TransportService
{
    private const ACCOUNT_CASH = '1000-CASH';
    private const ACCOUNT_TRANSPORT_REVENUE = '4100-TRANSPORT-REVENUE';

    public function __construct(private readonly LedgerService $ledger)
    {
    }

    public function recordTransportPayment(
        string $studentId,
        string $routeId,
        ?float $amount,
        string $method,
        User $actor
    ): ShuleTransportPayment {
        $schoolId = app(SchoolContext::class)->requireId();

        return DB::transaction(function () use ($schoolId, $studentId, $routeId, $amount, $method, $actor): ShuleTransportPayment {
            $studentSchoolId = DB::table('shule_students')
                ->where('id', $studentId)
                ->value('school_id');
            if (!$studentSchoolId || $studentSchoolId !== $schoolId) {
                throw new RuntimeException('Transport payment student must belong to active school.');
            }

            $route = ShuleRoute::query()
                ->where('school_id', $schoolId)
                ->where('id', $routeId)
                ->firstOrFail();

            $resolvedAmount = $amount ?? ($route->fee_amount !== null ? (float) $route->fee_amount : null);
            if ($resolvedAmount === null || $resolvedAmount <= 0) {
                throw new RuntimeException('Transport payment amount must be greater than zero.');
            }

            $payment = ShuleTransportPayment::query()->create([
                'student_id' => $studentId,
                'route_id' => $routeId,
                'amount' => round($resolvedAmount, 2),
                'method' => $method,
                'status' => 'posted',
                'paid_at' => now(),
                'recorded_by' => $actor->getKey(),
            ]);

            $this->ledger->postEvent(
                $schoolId,
                'transport_payment',
                (string) $payment->id,
                sprintf('Transport payment received for student %s', $studentId),
                [
                    [
                        'account_code' => self::ACCOUNT_CASH,
                        'debit' => $resolvedAmount,
                        'credit' => null,
                    ],
                    [
                        'account_code' => self::ACCOUNT_TRANSPORT_REVENUE,
                        'debit' => null,
                        'credit' => $resolvedAmount,
                    ],
                ]
            );

            return $payment;
        });
    }
}
