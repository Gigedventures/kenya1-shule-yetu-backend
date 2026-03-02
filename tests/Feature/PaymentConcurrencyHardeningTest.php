<?php

namespace Tests\Feature;

use App\Modules\ShuleYetu\Finance\Services\FeeService;
use App\Modules\ShuleYetu\Models\ShuleAcademicYear;
use App\Modules\ShuleYetu\Models\ShuleClass;
use App\Modules\ShuleYetu\Models\ShuleFeeStructure;
use App\Modules\ShuleYetu\Models\ShuleSchool;
use App\Modules\ShuleYetu\Models\ShuleStudent;
use App\Modules\ShuleYetu\Models\ShuleStudentBill;
use App\Modules\ShuleYetu\Models\ShuleStudentEnrollment;
use App\Modules\ShuleYetu\Models\ShuleTerm;
use App\Modules\ShuleYetu\Support\Tenancy\SchoolContext;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;
use Tests\TestCase;

class PaymentConcurrencyHardeningTest extends TestCase
{
    use RefreshDatabase;

    public function test_two_near_simultaneous_payments_cannot_over_allocate_or_break_order(): void
    {
        [$school, $student, $billOld, $billNew] = $this->seedStudentWithTwoBills();
        $service = app(FeeService::class);
        $user = $this->fakeUserWithPermission('finance.payments.record');

        $paymentOne = $service->recordPayment($student->id, 1000, 'cash', 'PAY-A', $user, 'idem-1');
        $this->assertNotNull($paymentOne->id);

        $defaultConnection = config('database.default');
        $defaultCfg = config("database.connections.{$defaultConnection}");
        $canUseSecondConnection = is_array($defaultCfg)
            && !(($defaultCfg['driver'] ?? null) === 'sqlite' && ($defaultCfg['database'] ?? null) === ':memory:');

        $secondFailed = false;

        if ($canUseSecondConnection) {
            // Use a second connection to simulate lock contention in a single test process.
            config(['database.connections.mysql2' => $defaultCfg]);
            DB::purge('mysql2');
            $connA = DB::connection($defaultConnection);
            $connB = DB::connection('mysql2');

            if (($defaultCfg['driver'] ?? null) === 'mysql') {
                $connB->statement('SET SESSION innodb_lock_wait_timeout = 1');
            }

            $connA->beginTransaction();
            try {
                $connA->table('shule_student_bills')
                    ->where('school_id', $school->id)
                    ->where('student_id', $student->id)
                    ->where('balance', '>', 0)
                    ->orderBy('created_at', 'asc')
                    ->lockForUpdate()
                    ->get();

                $previousDefault = config('database.default');
                DB::setDefaultConnection('mysql2');
                config(['database.default' => 'mysql2']);
                try {
                    try {
                        app(FeeService::class)->recordPayment($student->id, 1000, 'cash', 'PAY-B', $user, 'idem-2');
                    } catch (RuntimeException|QueryException) {
                        $secondFailed = true;
                    }
                } finally {
                    DB::setDefaultConnection($previousDefault);
                    config(['database.default' => $previousDefault]);
                }
            } finally {
                $connA->rollBack();
                DB::disconnect('mysql2');
            }
        }

        if (!$canUseSecondConnection) {
            // Lightweight fallback for environments where a second shared DB connection is not available.
            try {
                $service->recordPayment($student->id, 1000, 'cash', 'PAY-B', $user, 'idem-2');
            } catch (RuntimeException) {
                $secondFailed = true;
            }
        }

        $this->assertTrue($secondFailed, 'Second payment should fail safely when only 500 remains.');

        $billOld->refresh();
        $billNew->refresh();

        $totalPaid = (float) $billOld->paid_amount + (float) $billNew->paid_amount;
        $this->assertLessThanOrEqual(1500.0, $totalPaid);
        $this->assertGreaterThanOrEqual(0.0, (float) $billOld->balance);
        $this->assertGreaterThanOrEqual(0.0, (float) $billNew->balance);

        $allocations = DB::table('shule_payment_allocations')
            ->where('payment_id', $paymentOne->id)
            ->orderBy('created_at')
            ->get(['student_bill_id', 'allocated_amount']);
        $this->assertSame((string) $billOld->id, (string) $allocations->first()->student_bill_id);

        $idempotent = $service->recordPayment($student->id, 1000, 'cash', 'PAY-A-RETRY', $user, 'idem-1');
        $this->assertSame((string) $paymentOne->id, (string) $idempotent->id);
    }

    private function seedStudentWithTwoBills(): array
    {
        $school = ShuleSchool::query()->create([
            'name' => 'School HC',
            'code' => 'FN-HC-' . Str::upper(Str::random(5)),
            'status' => 'active',
        ]);
        app(SchoolContext::class)->setId($school->id);

        $year = ShuleAcademicYear::query()->create([
            'name' => '2026',
            'start_date' => now()->startOfYear(),
            'end_date' => now()->endOfYear(),
            'is_active' => true,
        ]);
        $termOld = ShuleTerm::query()->create([
            'academic_year_id' => $year->id,
            'name' => 'Term 1',
            'start_date' => now()->startOfYear(),
            'end_date' => now()->startOfYear()->addMonths(3),
        ]);
        $termNew = ShuleTerm::query()->create([
            'academic_year_id' => $year->id,
            'name' => 'Term 2',
            'start_date' => now()->startOfYear()->addMonths(4),
            'end_date' => now()->startOfYear()->addMonths(7),
        ]);
        $class = ShuleClass::query()->create([
            'name' => 'Grade 6',
            'level' => 6,
        ]);

        $structureOld = ShuleFeeStructure::query()->create([
            'school_id' => $school->id,
            'academic_year_id' => $year->id,
            'term_id' => $termOld->id,
            'class_id' => $class->id,
            'name' => 'Old Bill',
            'is_active' => true,
        ]);
        $structureNew = ShuleFeeStructure::query()->create([
            'school_id' => $school->id,
            'academic_year_id' => $year->id,
            'term_id' => $termNew->id,
            'class_id' => $class->id,
            'name' => 'New Bill',
            'is_active' => true,
        ]);

        $student = ShuleStudent::query()->create([
            'first_name' => 'Concurrent',
            'last_name' => 'Case',
            'current_class_id' => $class->id,
            'status' => 'active',
        ]);
        ShuleStudentEnrollment::query()->create([
            'student_id' => $student->id,
            'academic_year_id' => $year->id,
            'class_id' => $class->id,
            'status' => 'enrolled',
        ]);

        $billOld = ShuleStudentBill::query()->create([
            'school_id' => $school->id,
            'student_id' => $student->id,
            'fee_structure_id' => $structureOld->id,
            'total_amount' => 1000,
            'paid_amount' => 0,
            'balance' => 1000,
            'status' => 'unpaid',
            'created_at' => now()->subDays(10),
            'updated_at' => now()->subDays(10),
        ]);
        $billNew = ShuleStudentBill::query()->create([
            'school_id' => $school->id,
            'student_id' => $student->id,
            'fee_structure_id' => $structureNew->id,
            'total_amount' => 500,
            'paid_amount' => 0,
            'balance' => 500,
            'status' => 'unpaid',
            'created_at' => now()->subDays(1),
            'updated_at' => now()->subDays(1),
        ]);

        // Persist explicit bill age for deterministic oldest-first behavior.
        ShuleStudentBill::query()->where('id', $billOld->id)->update([
            'created_at' => now()->subDays(10),
            'updated_at' => now()->subDays(10),
        ]);
        ShuleStudentBill::query()->where('id', $billNew->id)->update([
            'created_at' => now()->subDays(1),
            'updated_at' => now()->subDays(1),
        ]);

        return [$school, $student, $billOld->fresh(), $billNew->fresh()];
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
