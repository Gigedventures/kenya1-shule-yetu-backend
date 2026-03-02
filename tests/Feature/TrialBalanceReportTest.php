<?php

namespace Tests\Feature;

use App\Modules\ShuleYetu\Finance\Services\FeeService;
use App\Modules\ShuleYetu\Models\ShuleAcademicYear;
use App\Modules\ShuleYetu\Models\ShuleClass;
use App\Modules\ShuleYetu\Models\ShuleExpense;
use App\Modules\ShuleYetu\Models\ShuleExpenseCategory;
use App\Modules\ShuleYetu\Models\ShuleFeeItem;
use App\Modules\ShuleYetu\Models\ShuleFeeStructure;
use App\Modules\ShuleYetu\Models\ShuleFinancialYear;
use App\Modules\ShuleYetu\Models\ShuleSchool;
use App\Modules\ShuleYetu\Models\ShuleStudent;
use App\Modules\ShuleYetu\Models\ShuleStudentEnrollment;
use App\Modules\ShuleYetu\Models\ShuleTerm;
use App\Modules\ShuleYetu\Support\Tenancy\SchoolContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class TrialBalanceReportTest extends TestCase
{
    use RefreshDatabase;

    public function test_trial_balance_is_balanced_for_date_range(): void
    {
        $school = ShuleSchool::query()->create([
            'name' => 'School TB',
            'code' => 'TB-' . Str::upper(Str::random(5)),
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
            'name' => 'Grade 2',
            'level' => 2,
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
            'first_name' => 'Trial',
            'last_name' => 'Balance',
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
        $user = $this->fakeUserWithPermission('finance.manage');
        $service->recordPayment($student->id, 400, 'cash', null, $user);

        $fy = ShuleFinancialYear::query()->create([
            'name' => 'FY 2026',
            'start_date' => now()->startOfYear(),
            'end_date' => now()->endOfYear(),
            'is_active' => true,
        ]);
        $category = ShuleExpenseCategory::query()->create([
            'name' => 'Admin',
            'expense_account_code' => '5000-EXPENSE',
        ]);
        $expense = ShuleExpense::query()->create([
            'financial_year_id' => $fy->id,
            'category_id' => $category->id,
            'amount' => 150,
            'description' => 'Stationery',
            'expense_date' => now()->toDateString(),
            'status' => 'approved',
            'created_by' => $user->id,
        ]);
        $service->postExpense($expense->id, $user);

        $trial = $service->getTrialBalance(now()->subDay()->toDateString(), now()->addDay()->toDateString());
        $this->assertTrue($trial['is_balanced']);
        $this->assertSame((float) $trial['total_debit'], (float) $trial['total_credit']);
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
