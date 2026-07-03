<?php

namespace App\K1\Ai\Core\LearningLoop;

use App\Modules\ShuleYetu\Support\Tenancy\SchoolContext;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * TeacherFeedbackCollector — Primary AI training signal.
 *
 * Captures teacher interaction data (lesson_id, teacher_id, grade, subject,
 * status, difficulty_rating, student_understanding, notes).
 * All AI learning and adaptation starts from this data.
 *
 * @package App\K1\Ai\Core\LearningLoop
 */
class TeacherFeedbackCollector
{
    /**
     * Record a single piece of teacher feedback.
     *
     * @param array $input {
     *   lesson_id, teacher_id, grade, subject, status,
     *   difficulty_rating (1-5), student_understanding (1-5), notes
     * }
     * @return array{feedback_id: string, recorded: bool}
     */
    public function collect(array $input): array
    {
        $schoolId = app(SchoolContext::class)->requireId();

        $feedbackId = DB::table('k1_teacher_feedback')->insertGetId([
            'school_id'           => $schoolId,
            'lesson_id'           => $input['lesson_id'],
            'teacher_id'          => $input['teacher_id'],
            'grade'               => $input['grade'],
            'subject'             => $input['subject'],
            'status'              => $input['status'] ?? 'used',
            'difficulty_rating'   => min(max($input['difficulty_rating'] ?? 3, 1), 5),
            'student_understanding' => min(max($input['student_understanding'] ?? 3, 1), 5),
            'notes'              => $input['notes'] ?? '',
            'created_at'         => now(),
        ]);

        return [
            'feedback_id' => (string) $feedbackId,
            'recorded'     => true,
        ];
    }

    /**
     * Get feedback summary for a school or teacher.
     */
    public function getSummary(?string $schoolId = null, ?string $teacherId = null): array
    {
        $query = DB::table('k1_teacher_feedback');

        if ($schoolId) {
            $query->where('school_id', $schoolId);
        }
        if ($teacherId) {
            $query->where('teacher_id', $teacherId);
        }

        return [
            'total_feedback'   => $query->count(),
            'avg_difficulty'   => round($query->avg('difficulty_rating') ?? 0.0, 2),
            'avg_understanding' => round($query->avg('student_understanding') ?? 0.0, 2),
            'by_status'       => $query->select('status')
                ->selectRaw('COUNT(*) as count')
                ->groupBy('status')
                ->get()
                ->pluck('count', 'status')
                ->toArray(),
        ];
    }
}