<?php

namespace Tests\Feature;

use App\Modules\ShuleYetu\Models\ShuleAcademicYear;
use App\Modules\ShuleYetu\Models\ShuleSchool;
use App\Modules\ShuleYetu\Support\Tenancy\SchoolContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class ActiveYearRuleTest extends TestCase
{
    use RefreshDatabase;

    public function test_only_one_active_academic_year_per_school(): void
    {
        $school = ShuleSchool::query()->create([
            'name' => 'Rule School',
            'code' => 'RULE-' . Str::upper(Str::random(5)),
            'status' => 'active',
        ]);

        app(SchoolContext::class)->setId($school->id);

        $firstYear = ShuleAcademicYear::query()->create([
            'name' => '2026',
            'start_date' => '2026-01-01',
            'end_date' => '2026-12-31',
            'is_active' => true,
        ]);

        $secondYear = ShuleAcademicYear::query()->create([
            'name' => '2027',
            'start_date' => '2027-01-01',
            'end_date' => '2027-12-31',
            'is_active' => true,
        ]);

        $firstYear->refresh();
        $secondYear->refresh();

        $this->assertFalse($firstYear->is_active);
        $this->assertTrue($secondYear->is_active);
    }
}

