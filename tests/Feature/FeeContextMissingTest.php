<?php

namespace Tests\Feature;

use App\Modules\ShuleYetu\Models\ShuleFeeStructure;
use App\Modules\ShuleYetu\Models\ShuleSchool;
use App\Modules\ShuleYetu\Support\Tenancy\SchoolContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use RuntimeException;
use Tests\TestCase;

class FeeContextMissingTest extends TestCase
{
    use RefreshDatabase;

    public function test_fee_queries_fail_closed_without_context(): void
    {
        $school = ShuleSchool::query()->create([
            'name' => 'School Z',
            'code' => 'FN-Z-' . Str::upper(Str::random(5)),
            'status' => 'active',
        ]);

        $context = app(SchoolContext::class);
        $context->setId($school->id);

        $year = \App\Modules\ShuleYetu\Models\ShuleAcademicYear::query()->create([
            'name' => '2026',
            'start_date' => now()->startOfYear(),
            'end_date' => now()->endOfYear(),
            'is_active' => true,
        ]);
        $term = \App\Modules\ShuleYetu\Models\ShuleTerm::query()->create([
            'academic_year_id' => $year->id,
            'name' => 'Term 1',
            'start_date' => now()->startOfYear(),
            'end_date' => now()->startOfYear()->addMonths(3),
        ]);
        $class = \App\Modules\ShuleYetu\Models\ShuleClass::query()->create([
            'name' => 'Grade 1',
            'level' => 1,
        ]);
        ShuleFeeStructure::query()->create([
            'academic_year_id' => $year->id,
            'term_id' => $term->id,
            'class_id' => $class->id,
            'name' => 'Fees',
            'is_active' => true,
        ]);

        $context->clear();

        $this->assertSame(0, ShuleFeeStructure::query()->count());
    }

    public function test_fee_create_requires_context(): void
    {
        $this->expectException(RuntimeException::class);

        ShuleFeeStructure::query()->create([
            'academic_year_id' => (string) \Illuminate\Support\Str::uuid(),
            'term_id' => (string) \Illuminate\Support\Str::uuid(),
            'class_id' => (string) \Illuminate\Support\Str::uuid(),
            'name' => 'Fees',
            'is_active' => true,
        ]);
    }
}
