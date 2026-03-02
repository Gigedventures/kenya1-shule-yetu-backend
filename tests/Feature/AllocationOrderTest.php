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
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Tests\TestCase;

class AllocationOrderTest extends TestCase
{
    use RefreshDatabase;

    public function test_payment_applies_to_oldest_bill_first(): void
    {
        $school = ShuleSchool::query()->create([
            'name' => 'School O',
            'code' => 'FN-O-' . Str::upper(Str::random(5)),
            'status' => 'active',
        ]);
        $context = app(SchoolContext::class);
        $context->setId($school->id);

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
        $term2 = ShuleTerm::query()->create([
            'academic_year_id' => $year->id,
            'name' => 'Term 2',
            'start_date' => now()->startOfYear()->addMonths(4),
            'end_date' => now()->startOfYear()->addMonths(7),
        ]);
        $class = ShuleClass::query()->create([
            'name' => 'Grade 1',
            'level' => 1,
        ]);

        $oldStructure = ShuleFeeStructure::query()->create([
            'school_id' => $school->id,
            'academic_year_id' => $year->id,
            'term_id' => $term->id,
            'class_id' => $class->id,
            'name' => 'Fees Term 1',
            'is_active' => true,
        ]);
        $newStructure = ShuleFeeStructure::query()->create([
            'school_id' => $school->id,
            'academic_year_id' => $year->id,
            'term_id' => $term2->id,
            'class_id' => $class->id,
            'name' => 'Fees Term 2',
            'is_active' => true,
        ]);

        $student = ShuleStudent::query()->create([
            'first_name' => 'Order',
            'last_name' => 'Test',
            'current_class_id' => $class->id,
            'status' => 'active',
        ]);
        ShuleStudentEnrollment::query()->create([
            'student_id' => $student->id,
            'academic_year_id' => $year->id,
            'class_id' => $class->id,
            'status' => 'enrolled',
        ]);

        $billOld = new ShuleStudentBill([
            'school_id' => $school->id,
            'student_id' => $student->id,
            'fee_structure_id' => $oldStructure->id,
            'total_amount' => 1000,
            'paid_amount' => 0,
            'balance' => 1000,
            'status' => 'unpaid',
        ]);
        $billOld->created_at = Carbon::now()->subDays(10);
        $billOld->updated_at = Carbon::now()->subDays(10);
        $billOld->save();
        ShuleStudentBill::query()->where('id', $billOld->id)->update([
            'created_at' => Carbon::now()->subDays(10),
            'updated_at' => Carbon::now()->subDays(10),
        ]);
        $billOld->refresh();

        $billNew = new ShuleStudentBill([
            'school_id' => $school->id,
            'student_id' => $student->id,
            'fee_structure_id' => $newStructure->id,
            'total_amount' => 1000,
            'paid_amount' => 0,
            'balance' => 1000,
            'status' => 'unpaid',
        ]);
        $billNew->created_at = Carbon::now()->subDays(1);
        $billNew->updated_at = Carbon::now()->subDays(1);
        $billNew->save();
        ShuleStudentBill::query()->where('id', $billNew->id)->update([
            'created_at' => Carbon::now()->subDays(1),
            'updated_at' => Carbon::now()->subDays(1),
        ]);
        $billNew->refresh();

        $service = app(FeeService::class);
        $user = $this->fakeUserWithPermission('finance.payments.record');
        $payment = $service->recordPayment($student->id, 1200, 'cash', null, $user);

        $allocations = $payment->allocations()->orderBy('created_at')->get();
        $this->assertSame((string) $billOld->id, (string) $allocations->first()->student_bill_id);
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
