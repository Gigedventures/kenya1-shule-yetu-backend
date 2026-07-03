<?php

namespace App\K1\Ai\Shared;

use App\K1\Ai\Core\LearningLoop\LessonOutcomeTracker;
use App\Modules\ShuleYetu\Support\Tenancy\SchoolContext;
use Illuminate\Support\Facades\DB;

/**
 * CrossSchoolComparator — Compares student/teacher/school performance across institutions.
 *
 * Supports anonymized comparisons where required.
 *
 * @package App\K1\Ai\Shared
 */
class CrossSchoolComparator
{
    /**
     * Compare a school against all others.
     *
     * @param string $schoolId
     * @return array{comparisons: array, percentile: float, rank: int}
     */
    public function compare(string $schoolId): array
    {
        $allSchools = DB::table('k1_lesson_outcomes')
            ->select('school_id')
            ->selectRaw('AVG(effectiveness) as avg_effectiveness')
            ->groupBy('school_id')
            ->orderByDesc('avg_effectiveness')
            ->get()
            ->toArray();

        $schoolEffectiveness = DB::table('k1_lesson_outcomes')
            ->where('school_id', $schoolId)
            ->avg('effectiveness') ?? 0;

        $position = array_search($schoolId, array_column((array) $allSchools, 'school_id'));
        $total = count($allSchools);

        return [
            'comparisons' => $allSchools,
            'percentile'  => $total > 0 ? round((1 - $position / $total) * 100) : 0,
            'rank'        => $position + 1,
        ];
    }
}