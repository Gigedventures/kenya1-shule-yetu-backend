<?php

namespace Tests\Feature;

use App\Modules\ShuleYetu\Models\ShuleAcademicYear;
use App\Modules\ShuleYetu\Models\ShuleClass;
use App\Modules\ShuleYetu\Models\ShuleFeeStructure;
use App\Modules\ShuleYetu\Models\ShuleSchool;
use App\Modules\ShuleYetu\Models\ShuleTerm;
use App\Modules\ShuleYetu\Support\Tenancy\SchoolContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class FinanceIsolationTest extends TestCase
{
    use RefreshDatabase;

    public function test_finance_records_are_isolated_by_school(): void
    {
        $schoolA = ShuleSchool::query()->create([
            'name' => 'School A',
            'code' => 'FN-A-' . Str::upper(Str::random(5)),
            'status' => 'active',
        ]);
        $schoolB = ShuleSchool::query()->create([
            'name' => 'School B',
            'code' => 'FN-B-' . Str::upper(Str::random(5)),
            'status' => 'active',
        ]);

        $context = app(SchoolContext::class);

        $context->setId($schoolA->id);
        [$yearA, $termA, $classA] = $this->makeAcademics();
        ShuleFeeStructure::query()->create([
            'academic_year_id' => $yearA->id,
            'term_id' => $termA->id,
            'class_id' => $classA->id,
            'name' => 'A Fees',
            'is_active' => true,
        ]);

        $context->setId($schoolB->id);
        [$yearB, $termB, $classB] = $this->makeAcademics();
        ShuleFeeStructure::query()->create([
            'academic_year_id' => $yearB->id,
            'term_id' => $termB->id,
            'class_id' => $classB->id,
            'name' => 'B Fees',
            'is_active' => true,
        ]);

        $context->setId($schoolA->id);
        $this->assertSame(1, ShuleFeeStructure::query()->count());

        $context->setId($schoolB->id);
        $this->assertSame(1, ShuleFeeStructure::query()->count());
    }

    private function makeAcademics(): array
    {
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

        return [$year, $term, $class];
    }
}
