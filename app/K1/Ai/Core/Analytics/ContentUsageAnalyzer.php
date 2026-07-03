<?php

namespace App\K1\Ai\Core\Analytics;

use App\Modules\ShuleYetu\Support\Tenancy\SchoolContext;
use Illuminate\Support\Facades\DB;

/**
 * ContentUsageAnalyzer — Measures how lesson plans and AI content are being used.
 *
 * Tracks:
 * - usage per subject
 * - average teacher rating
 * - content reusability score
 *
 * @package App\K1\Ai\Core\Analytics
 */
class ContentUsageAnalyzer
{
    /**
     * Get content usage metrics for a school.
     *
     * @return array{total_content: int, avg_rating: float, top_subjects: string[], reused_pct: float}
     */
    public function analyze(?string $schoolId = null): array
    {
        $schoolId ??= app(SchoolContext::class)->requireId();

        $feedback = DB::table('k1_teacher_feedback')
            ->where('school_id', $schoolId)
            ->selectRaw('
                COUNT(*) as total,
                COUNT(DISTINCT lesson_id) as unique_lessons,
                AVG(student_understanding) as avg_understanding
            ')
            ->first();

        $reused = DB::table('k1_teacher_feedback')
            ->where('school_id', $schoolId)
            ->where('status', 'used')
            ->count();

        return [
            'total_content'    => (int) ($feedback->total ?? 0),
            'unique_lessons'   => (int) ($feedback->unique_lessons ?? 0),
            'avg_rating'       => round((float) ($feedback->avg_understanding ?? 3), 2),
            'reused_pct'      => $feedback->total > 0 ? round(($reused / $feedback->total) * 100, 1) : 0.0,
        ];
    }
}