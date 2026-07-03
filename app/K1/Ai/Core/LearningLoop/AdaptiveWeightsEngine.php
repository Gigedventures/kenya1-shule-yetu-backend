<?php

namespace App\K1\Ai\Core\LearningLoop;

use App\Modules\ShuleYetu\Support\Tenancy\SchoolContext;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * AdaptiveWeightsEngine — Dynamically adjusts AI generation behavior.
 *
 * Based on:
 * - engagement levels
 * - effectiveness scores
 * - teacher feedback patterns
 *
 * Algorithm:
 *   low_engagement  → increase activities, reduce theory
 *   high_improvement → reinforce pattern used
 *   high_difficulty   → reduce difficulty, add scaffolding
 *
 * @package App\K1\Ai\Core\LearningLoop
 */
class AdaptiveWeightsEngine
{
    private const ADJUSTMENT_RULES = [
        'low_engagement'     => ['increase_activities' => 0.2, 'reduce_theory' => 0.15],
        'high_improvement'  => ['reinforce_pattern' => 0.3],
        'high_difficulty'   => ['add_scaffolding' => 0.25, 'reduce_difficulty' => 0.1],
    ];

    /**
     * Calculate adaptive weights for a subject/grade based on historical data.
     *
     * @return array{
     *     subject: string,
     *     grade: string,
     *     weights: array{activities: float, theory: float, scaffolding: float},
     *     adjustments: string[]
     * }
     */
    public function calculateWeights(string $subject, string $grade, ?string $schoolId = null): array
    {
        $schoolId ??= app(SchoolContext::class)->requireId();

        $feedback = DB::table('k1_teacher_feedback')
            ->where('school_id', $schoolId)
            ->where('subject', $subject)
            ->selectRaw('
                AVG(CAST(difficulty_rating AS FLOAT)) as avg_difficulty,
                AVG(CAST(student_understanding AS FLOAT)) as avg_understanding
            ')
            ->first();

        $outcomes = DB::table('k1_lesson_outcomes')
            ->where('school_id', $schoolId)
            ->where('subject', $subject)
            ->selectRaw('
                AVG(CAST(effectiveness AS FLOAT)) as avg_effectiveness,
                AVG(CAST(engagement_score AS FLOAT)) as avg_engagement
            ')
            ->first();

        $difficulty    = (float) ($feedback->avg_difficulty ?? 3.0);
        $understanding = (float) ($feedback->avg_understanding ?? 3.0);
        $effectiveness = (float) ($outcomes->avg_effectiveness ?? 50.0);
        $engagement    = (float) ($outcomes->avg_engagement ?? 50.0);

        $adjustments = [];

        // Default weights
        $weights = [
            'activities' => 0.5,
            'theory'     => 0.3,
            'scaffolding' => 0.2,
        ];

        // Low engagement → shift to activities
        if ($engagement < 40) {
            $weights['activities'] += 0.2;
            $weights['theory']    -= 0.1;
            $adjustments[] = 'Low engagement detected — shifted weight to activities';
        }

        // High difficulty → add scaffolding
        if ($difficulty >= 4) {
            $weights['scaffolding'] += 0.25;
            $weights['theory']     -= 0.1;
            $adjustments[] = 'High difficulty detected — added scaffolding support';
        }

        // High effectiveness → reinforce current pattern
        if ($effectiveness > 70) {
            $weights['activities'] += 0.1;
            $adjustments[] = 'High effectiveness — reinforcing activity-heavy pattern';
        }

        // Normalize to 1.0
        $total = array_sum($weights);
        if ($total > 0) {
            foreach ($weights as $k => $v) {
                $weights[$k] = round($v / $total, 3);
            }
        }

        return [
            'subject'     => $subject,
            'grade'      => $grade,
            'weights'    => $weights,
            'adjustments' => $adjustments,
        ];
    }
}