<?php

namespace Tests\Feature;

use App\Modules\ShuleYetu\Models\ShuleFinancialYear;
use App\Modules\ShuleYetu\Models\ShuleSchool;
use App\Modules\ShuleYetu\Support\Tenancy\SchoolContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use RuntimeException;
use Tests\TestCase;

class FinancialYearActivationUniquenessTest extends TestCase
{
    use RefreshDatabase;

    public function test_only_one_active_financial_year_per_school(): void
    {
        $school = ShuleSchool::query()->create([
            'name' => 'School FY',
            'code' => 'FY-' . Str::upper(Str::random(5)),
            'status' => 'active',
        ]);
        app(SchoolContext::class)->setId($school->id);

        ShuleFinancialYear::query()->create([
            'name' => 'FY 2026',
            'start_date' => now()->startOfYear(),
            'end_date' => now()->endOfYear(),
            'is_active' => true,
        ]);

        $this->expectException(RuntimeException::class);
        ShuleFinancialYear::query()->create([
            'name' => 'FY 2027',
            'start_date' => now()->addYear()->startOfYear(),
            'end_date' => now()->addYear()->endOfYear(),
            'is_active' => true,
        ]);
    }
}
