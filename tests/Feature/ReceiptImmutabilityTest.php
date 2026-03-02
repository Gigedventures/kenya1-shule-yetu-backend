<?php

namespace Tests\Feature;

use App\Modules\ShuleYetu\Finance\Services\FeeService;
use App\Modules\ShuleYetu\Models\ShuleAcademicYear;
use App\Modules\ShuleYetu\Models\ShuleClass;
use App\Modules\ShuleYetu\Models\ShuleFeeItem;
use App\Modules\ShuleYetu\Models\ShuleFeeStructure;
use App\Modules\ShuleYetu\Models\ShuleReceipt;
use App\Modules\ShuleYetu\Models\ShuleSchool;
use App\Modules\ShuleYetu\Models\ShuleStudent;
use App\Modules\ShuleYetu\Models\ShuleStudentEnrollment;
use App\Modules\ShuleYetu\Models\ShuleTerm;
use App\Modules\ShuleYetu\Support\Tenancy\SchoolContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use RuntimeException;
use Tests\TestCase;

class ReceiptImmutabilityTest extends TestCase
{
    use RefreshDatabase;

    public function test_receipt_is_created_and_is_immutable(): void
    {
        $school = ShuleSchool::query()->create([
            'name' => 'School R',
            'code' => 'RCPT-' . Str::upper(Str::random(5)),
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
            'name' => 'Grade 1',
            'level' => 1,
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
            'first_name' => 'Receipt',
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

        $service = app(FeeService::class);
        $service->generateBillsForStructure($structure->id, $school->id);
        $user = $this->fakeUserWithPermission('finance.payments.record');
        $payment = $service->recordPayment($student->id, 500, 'cash', null, $user);

        $receipt = ShuleReceipt::query()->where('payment_id', $payment->id)->firstOrFail();
        $this->assertNotNull($receipt->receipt_number);

        $this->expectException(RuntimeException::class);
        $receipt->update(['receipt_number' => 'MUTATED']);
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
