<?php

namespace App\K1\Ai\Policy;

use App\Modules\ShuleYetu\Models\ShuleStudentBill;
use App\Modules\ShuleYetu\Support\Tenancy\SchoolContext;

class BudgetImpactAnalyzer
{
    public function analyze(float $budgetChange, string $region): array
    {
        $schoolId = app(SchoolContext::class)->requireId();
        $outstanding = ShuleStudentBill::query()->where('school_id', $schoolId)->sum('balance');
        $impact = $outstanding * ($budgetChange / 100);

        return [
            'budget_change' => $budgetChange,
            'region' => $region,
            'current_outstanding' => $outstanding,
            'projected_balance' => round($outstanding - $impact, 2),
            'schools_affected' => 1,
            'recommendation' => "{$budgetChange}% budget reduction will save KES " . round($impact, 2),
        ];
    }
}