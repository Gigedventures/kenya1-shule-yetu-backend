<?php

namespace Tests\Feature;

use App\Modules\ShuleYetu\Finance\Services\FeeService;
use App\Modules\ShuleYetu\Models\ShuleAcademicYear;
use App\Modules\ShuleYetu\Models\ShuleClass;
use App\Modules\ShuleYetu\Models\ShuleFeeItem;
use App\Modules\ShuleYetu\Models\ShuleFeeStructure;
use App\Modules\ShuleYetu\Models\ShuleSchool;
use App\Modules\ShuleYetu\Models\ShuleStudent;
use App\Modules\ShuleYetu\Models\ShuleStudentEnrollment;
use App\Modules\ShuleYetu\Models\ShuleTerm;
use App\Modules\ShuleYetu\Support\Tenancy\SchoolContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use RuntimeException;
use Tests\TestCase;

class CrossSchoolFinanceProtectionTest extends TestCase
{
    use RefreshDatabase;

    public function test_cross_school_payment_is_blocked(): void
    {
        $schoolA = ShuleSchool::query()->create([
            'name' => 'School A',
            'code' => 'FN-XA-' . Str::upper(Str::random(5)),
            'status' => 'active',
        ]);
        $schoolB = ShuleSchool::query()->create([
            'name' => 'School B',
            'code' => 'FN-XB-' . Str::upper(Str::random(5)),
            'status' => 'active',
        ]);

        $context = app(SchoolContext::class);
        $context->setId($schoolA->id);

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
            'name' => 'Grade 1',
            'level' => 1,
        ]);

        $structure = ShuleFeeStructure::query()->create([
            'academic_year_id' => $year->id,
            'term_id' => $term->id,
            'class_id' => $class->id,
            'name' => 'Fees',
            'is_active' => true,
        ]);
        ShuleFeeItem::query()->create([
            'fee_structure_id' => $structure->id,
            'name' => 'Tuition',
            'amount' => 3000,
            'is_mandatory' => true,
        ]);

        $studentA = ShuleStudent::query()->create([
            'first_name' => 'School',
            'last_name' => 'A',
            'current_class_id' => $class->id,
            'status' => 'active',
        ]);
        ShuleStudentEnrollment::query()->create([
            'student_id' => $studentA->id,
            'academic_year_id' => $year->id,
            'class_id' => $class->id,
            'status' => 'enrolled',
        ]);

        $service = app(FeeService::class);
        $service->generateBillsForStructure($structure->id, $schoolA->id);

        $context->setId($schoolB->id);
        $classB = ShuleClass::query()->create([
            'name' => 'Grade 2',
            'level' => 2,
        ]);
        $yearB = ShuleAcademicYear::query()->create([
            'name' => '2026B',
            'start_date' => now()->startOfYear(),
            'end_date' => now()->endOfYear(),
            'is_active' => true,
        ]);
        $termB = ShuleTerm::query()->create([
            'academic_year_id' => $yearB->id,
            'name' => 'Term 1',
            'start_date' => now()->startOfYear(),
            'end_date' => now()->startOfYear()->addMonths(3),
        ]);
        $studentB = ShuleStudent::query()->create([
            'first_name' => 'School',
            'last_name' => 'B',
            'current_class_id' => $classB->id,
            'status' => 'active',
        ]);
        ShuleStudentEnrollment::query()->create([
            'student_id' => $studentB->id,
            'academic_year_id' => $yearB->id,
            'class_id' => $classB->id,
            'status' => 'enrolled',
        ]);

        $context->setId($schoolA->id);

        $this->expectException(RuntimeException::class);

        $service->recordPayment($studentB->id, 500, 'cash', null, $this->fakeUserWithPermission('finance.payments.record'));
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
