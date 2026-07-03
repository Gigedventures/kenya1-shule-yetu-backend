<?php

namespace App\K1\Ai\National;

use Illuminate\Support\Facades\DB;

/**
 * EducationTrendAnalyzer — Detects national-level education trends.
 *
 * Tracks:
 *   - rising/falling subjects
 *   - curriculum effectiveness trends
 *   - learning outcome shifts over time
 *
 * @package App\K1\Ai\National
 */
class EducationTrendAnalyzer
{
    /**
     * Analyze national education trends.
     *
     * @return array{rising_subjects: string[], falling_subjects: string[], stable_subjects: string[], avg_trend: float}
     */
    public function analyze(): array
    {
        $trends = DB::table('k1_lesson_outcomes')
            ->select('subject')
            ->selectRaw('
                AVG(effectiveness) as avg_effectiveness,
                COUNT(*) as total
            ')
            ->groupBy('subject')
            ->orderByDesc('avg_effectiveness')
            ->get()
            ->toArray();

        $all = collect($trends);
        $avg = $all->avg('avg_effectiveness');

        return [
            'rising_subjects'  => $all->filter(fn($t) => ($t->avg_effectiveness ?? 0) > $avg)->pluck('subject')->toArray(),
            'falling_subjects' => $all->filter(fn($t) => ($t->avg_effectiveness ?? 0) < $avg)->pluck('subject')->toArray(),
            'stable_subjects'  => $all->filter(fn($t) => abs(($t->avg_effectiveness ?? 0) - $avg) < 5)->pluck('subject')->toArray(),
            'avg_trend'        => round($avg, 2),
        ];
    }
}