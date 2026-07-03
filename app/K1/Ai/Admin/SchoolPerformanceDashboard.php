<?php

namespace App\K1\Ai\Admin;

use App\K1\Ai\Core\LearningLoop\LessonOutcomeTracker;
use Illuminate\Support\Facades\DB;

/**
 * SchoolPerformanceDashboard — School admin intelligence dashboard.
 *
 * Provides:
 *   - staff performance ranking
 *   - subject performance heatmaps
 *   - curriculum efficiency score
 *
 * @package App\K1\Ai\Admin
 */
class SchoolPerformanceDashboard
{
    /**
     * Generate a full school performance dashboard.
     *
     * @param string $schoolId
     * @return array{dashboard: array, staff_ranking: array, subject_heatmap: array, curriculum_score: float}
     */
    public function build(string $schoolId): array
    {
        $outcomes = DB::table('k1_lesson_outcomes')
            ->where('school_id', $schoolId)
            ->select('subject')
            ->selectRaw('
                AVG(effectiveness) as avg_effectiveness,
                COUNT(*) as lesson_count,
                AVG(engagement_score) as avg_engagement
            ')
            ->groupBy('subject')
            ->get()
            ->toArray();
        $subjects = collect($outcomes);

        return [
            'dashboard' => [
                'total_lessons'     => (int) $subjects->sum('lesson_count'),
                'avg_effectiveness'  => round((float) $subjects->avg('avg_effectiveness'), 2),
                'avg_engagement'     => (int) $subjects->avg('avg_engagement'),
            ],
            'staff_ranking' => $outcomes,
            'subject_heatmap' => $subjects->sortByDesc('avg_effectiveness')->values()->toArray(),
            'curriculum_score' => round((float) $subjects->avg('avg_effectiveness') ?? 0, 2),
        ];
    }
}