<?php

namespace App\K1\Ai\Core\LearningLoop;

use App\Modules\ShuleYetu\Support\Tenancy\SchoolContext;
use App\Modules\ShuleYetu\Models\ShuleExamScore;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * LessonOutcomeTracker — Tracks real classroom performance.
 *
 * Correlates lesson -> student outcome via:
 * - pre/post test improvement
 * - engagement score
 * - retention rate
 * - effectiveness score (0-100)
 *
 * @package App\K1\Ai\Core\LearningLoop
 */
class LessonOutcomeTracker
{
    /**
     * Record a lesson outcome for a specific lesson + student cohort.
     */
    public function track(array $input): array
    {
        $schoolId = app(SchoolContext::class)->requireId();

        $outcomeId = DB::table('k1_lesson_outcomes')->insertGetId([
            'school_id'         => $schoolId,
            'lesson_id'         => $input['lesson_id'],
            'grade'             => $input['grade'],
            'subject'           => $input['subject'],
            'pre_test'          => (float) ($input['pre_test'] ?? 0),
            'post_test'         => (float) ($input['post_test'] ?? 0),
            'engagement_score'  => min((int) ($input['engagement_score'] ?? 50), 100),
            'retention_rate'    => min((float) ($input['retention_rate'] ?? 0.5), 1.0),
            'effectiveness'     => $this->calculateEffectiveness(
                (float) ($input['pre_test'] ?? 0),
                (float) ($input['post_test'] ?? 0),
                (int) ($input['engagement_score'] ?? 50)
            ),
            'created_at'        => now(),
        ]);

        return [
            'outcome_id'      => (string) $outcomeId,
            'effectiveness'   => $this->calculateEffectiveness(
                (float) ($input['pre_test'] ?? 0),
                (float) ($input['post_test'] ?? 0),
                (int) ($input['engagement_score'] ?? 50)
            ),
            'improvement'     => max(0, ((float) ($input['post_test'] ?? 0) - (float) ($input['pre_test'] ?? 0))),
        ];
    }

    /**
     * Calculate effectiveness score.
     * Formula: (post_test_improvement * 0.6) + (engagement_score * 0.4)
     */
    private function calculateEffectiveness(float $pre, float $post, int $engagement): float
    {
        return match (true) {
            $pre <= 0 => 0.0,
            default => round((($post - $pre) * 0.6) + ($engagement * 0.4), 2),
        };
    }

    /**
     * Get average effectiveness by subject for a school.
     */
    public function getSubjectEffectiveness(?string $schoolId = null): array
    {
        $query = DB::table('k1_lesson_outcomes');

        if ($schoolId) {
            $query->where('school_id', $schoolId);
        }

        return $query->select('subject')
            ->selectRaw('AVG(effectiveness) as avg_effectiveness')
            ->selectRaw('COUNT(*) as lesson_count')
            ->groupBy('subject')
            ->orderByDesc('avg_effectiveness')
            ->get()
            ->toArray();
    }
}