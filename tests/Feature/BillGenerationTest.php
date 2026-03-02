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
use Tests\TestCase;

class BillGenerationTest extends TestCase
{
    use RefreshDatabase;

    public function test_bills_created_for_active_enrolled_students(): void
    {
        $school = ShuleSchool::query()->create([
            'name' => 'School F',
            'code' => 'FN-G-' . Str::upper(Str::random(5)),
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
        $class = ShuleClass::query()->create([
            'name' => 'Grade 2',
            'level' => 2,
        ]);

        $structure = ShuleFeeStructure::query()->create([
            'academic_year_id' => $year->id,
            'term_id' => $term->id,
            'class_id' => $class->id,
            'name' => 'Term 1 Fees',
            'is_active' => true,
        ]);

        ShuleFeeItem::query()->create([
            'fee_structure_id' => $structure->id,
            'name' => 'Tuition',
            'amount' => 5000,
            'is_mandatory' => true,
        ]);
        ShuleFeeItem::query()->create([
            'fee_structure_id' => $structure->id,
            'name' => 'Transport',
            'amount' => 1500,
            'is_mandatory' => true,
        ]);

        $studentA = ShuleStudent::query()->create([
            'first_name' => 'A',
            'last_name' => 'Student',
            'current_class_id' => $class->id,
            'status' => 'active',
        ]);
        $studentB = ShuleStudent::query()->create([
            'first_name' => 'B',
            'last_name' => 'Student',
            'current_class_id' => $class->id,
            'status' => 'active',
        ]);

        ShuleStudentEnrollment::query()->create([
            'student_id' => $studentA->id,
            'academic_year_id' => $year->id,
            'class_id' => $class->id,
            'status' => 'enrolled',
        ]);
        ShuleStudentEnrollment::query()->create([
            'student_id' => $studentB->id,
            'academic_year_id' => $year->id,
            'class_id' => $class->id,
            'status' => 'enrolled',
        ]);

        $service = app(FeeService::class);
        $created = $service->generateBillsForStructure($structure->id, $school->id);

        $this->assertSame(2, $created);

        $bill = \App\Modules\ShuleYetu\Models\ShuleStudentBill::query()
            ->where('school_id', $school->id)
            ->where('fee_structure_id', $structure->id)
            ->orderBy('student_id')
            ->firstOrFail();
        $this->assertSame('6500.00', $bill->total_amount);
        $this->assertSame('6500.00', $bill->balance);
        $this->assertSame('unpaid', $bill->status);
    }
}
