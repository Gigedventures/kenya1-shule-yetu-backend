<?php

namespace App\K1\Ai\National;

use App\K1\Ai\Core\LearningLoop\LessonOutcomeTracker;
use App\Modules\ShuleYetu\Support\Tenancy\SchoolContext;
use Illuminate\Support\Facades\DB;

/**
 * SchoolBenchmarkEngine — Compares schools using performance metrics.
 *
 * Output per school:
 *   - ranking_score (0-1000)
 *   - national_percentile
 *   - strong/weak subjects
 *   - trend_direction
 *
 * @package App\K1\Ai\National
 */
class SchoolBenchmarkEngine
{
    private LessonOutcomeTracker $outcomes;

    public function __construct()
    {
        $this->outcomes = app(LessonOutcomeTracker::class);
    }

    /**
     * Benchmark all schools in a system.
     *
     * @return array{rankings: array[], top_school: string|null, weakest_school: string|null}
     */
    public function benchmark(): array
    {
        $outcomes = DB::table('k1_lesson_outcomes')
            ->select('school_id')
            ->selectRaw('
                AVG(effectiveness) as avg_effectiveness,
                AVG(engagement_score) as avg_engagement,
                COUNT(*) as total_lessons
            ')
            ->groupBy('school_id')
            ->orderByDesc('avg_effectiveness')
            ->get()
            ->toArray();

        $rankings = [];
        $maxEffectiveness = max(array_column($outcomes, 'avg_effectiveness') ?: [1]);

        foreach ($outcomes as $i => $school) {
            $score = round(
                ((float) ($school->avg_effectiveness ?? 0) / max($maxEffectiveness, 1)) * 1000
            );
            $rankings[] = [
                'school_id'         => $school->school_id,
                'ranking_score'      => $score,
                'percentile'         => round((1 - ($i / max(count($outcomes), 1))) * 100),
                'total_lessons'     => (int) ($school->total_lessons ?? 0),
            ];
        }

        usort($rankings, fn ($a, $b) => $b['ranking_score'] <=> $a['ranking_score']);

        return [
            'rankings'       => $rankings,
            'top_school'    => $rankings[0]['school_id'] ?? null,
            'weakest_school' => $rankings[count($rankings) - 1]['school_id'] ?? null,
        ];
    }
}