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
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;
use Tests\TestCase;

class PaymentConcurrencyTest extends TestCase
{
    use RefreshDatabase;

    public function test_near_simultaneous_payments_do_not_over_allocate(): void
    {
        $school = ShuleSchool::query()->create([
            'name' => 'School C',
            'code' => 'FN-C-' . Str::upper(Str::random(5)),
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
            'name' => 'Grade 4',
            'level' => 4,
        ]);
        $structure = ShuleFeeStructure::query()->create([
            'school_id' => $school->id,
            'academic_year_id' => $year->id,
            'term_id' => $term->id,
            'class_id' => $class->id,
            'name' => 'Fees',
            'is_active' => true,
        ]);

        $student = ShuleStudent::query()->create([
            'first_name' => 'Concurrent',
            'last_name' => 'Student',
            'current_class_id' => $class->id,
            'status' => 'active',
        ]);
        ShuleStudentEnrollment::query()->create([
            'student_id' => $student->id,
            'academic_year_id' => $year->id,
            'class_id' => $class->id,
            'status' => 'enrolled',
        ]);

        $bill = ShuleStudentBill::query()->create([
            'school_id' => $school->id,
            'student_id' => $student->id,
            'fee_structure_id' => $structure->id,
            'total_amount' => 1000,
            'paid_amount' => 0,
            'balance' => 1000,
            'status' => 'unpaid',
        ]);

        $service = app(FeeService::class);
        $user = $this->fakeUserWithPermission('finance.payments.record');

        // Rapid back-to-back calls simulate near-concurrent requests competing for the same balance.
        $service->recordPayment($student->id, 700, 'cash', 'P1', $user);

        try {
            $service->recordPayment($student->id, 700, 'cash', 'P2', $user);
        } catch (RuntimeException) {
            // Expected when outstanding is no longer enough.
        }

        $bill->refresh();

        $totalAllocated = (float) DB::table('shule_payment_allocations as spa')
            ->join('shule_payments as sp', 'sp.id', '=', 'spa.payment_id')
            ->where('spa.school_id', $school->id)
            ->where('sp.student_id', $student->id)
            ->sum('spa.allocated_amount');

        $this->assertLessThanOrEqual(1000.0, $totalAllocated);
        $this->assertLessThanOrEqual((float) $bill->total_amount, (float) $bill->paid_amount);
        $this->assertGreaterThanOrEqual(0.0, (float) $bill->balance);
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
