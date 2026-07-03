<?php

namespace App\K1\Ai\Core\LearningLoop;

use App\Modules\ShuleYetu\Support\Tenancy\SchoolContext;
use Illuminate\Support\Facades\DB;

/**
 * InterventionEffectivenessScorer — Scores how effective teaching interventions are.
 *
 * Uses TeacherFeedback + LessonOutcome data to calculate an effectiveness score
 * per subject/grade. This is the core signal for the AdaptiveWeightsEngine.
 *
 * @package App\K1\Ai\Core\LearningLoop
 */
class InterventionEffectivenessScorer
{
    /**
     * Score the effectiveness of a specific intervention strategy.
     */
    public function score(string $subject, string $grade, ?string $schoolId = null): array
    {
        $schoolId ??= app(SchoolContext::class)->requireId();

        $outcomes = DB::table('k1_lesson_outcomes')
            ->where('school_id', $schoolId)
            ->where('subject', $subject)
            ->where('grade', $grade)
            ->selectRaw('
                AVG(effectiveness) as avg_effectiveness,
                AVG(CAST(engagement_score AS FLOAT)) as avg_engagement,
                AVG(CAST(retention_rate AS FLOAT)) as avg_retention,
                COUNT(*) as total_lessons
            ')
            ->first();

        $feedback = DB::table('k1_teacher_feedback')
            ->where('school_id', $schoolId)
            ->where('subject', $subject)
            ->where('grade', $grade)
            ->selectRaw('
                AVG(CAST(difficulty_rating AS FLOAT)) as avg_difficulty,
                AVG(CAST(student_understanding AS FLOAT)) as avg_understanding,
                COUNT(*) as total_feedback
            ')
            ->first();

        return [
            'subject'          => $subject,
            'grade'           => $grade,
            'effectiveness'   => round((float) ($outcomes->avg_effectiveness ?? 0), 2),
            'engagement'      => (int) ($outcomes->avg_engagement ?? 0),
            'retention'       => round((float) ($outcomes->avg_retention ?? 0), 2),
            'difficulty'      => round((float) ($feedback->avg_difficulty ?? 3), 2),
            'understanding'   => round((float) ($feedback->avg_understanding ?? 3), 2),
            'lessons'        => (int) ($outcomes->total_lessons ?? 0),
        ];
    }
}