<?php

namespace App\K1\Ai\Core\Analytics;

use App\Modules\ShuleYetu\Support\Tenancy\SchoolContext;
use Illuminate\Support\Facades\DB;

/**
 * ClassroomPerformanceAnalyzer — Identifies weak subjects per school.
 *
 * Measures:
 * - subject-level effectiveness
 * - content usage patterns
 * - teacher feedback correlation
 *
 * @package App\K1\Ai\Core\Analytics
 */
class ClassroomPerformanceAnalyzer
{
    /**
     * Get the performance ranking of all subjects for a school.
     *
     * @return array{ranking: array[], weakest: string[], strongest: string[]}
     */
    public function analyze(?string $schoolId = null): array
    {
        $schoolId ??= app(SchoolContext::class)->requireId();

        $subjects = DB::table('k1_lesson_outcomes')
            ->where('school_id', $schoolId)
            ->select('subject')
            ->selectRaw('
                AVG(effectiveness) as avg_effectiveness,
                COUNT(*) as lesson_count,
                AVG(engagement_score) as avg_engagement
            ')
            ->groupBy('subject')
            ->orderByDesc('avg_effectiveness')
            ->get()
            ->toArray();

        return [
            'ranking'    => $subjects,
            'weakest'   => array_slice(array_column($subjects, 'subject'), -3) ?? [],
            'strongest' => array_slice(array_column($subjects, 'subject'), 0, 3) ?? [],
        ];
    }
}