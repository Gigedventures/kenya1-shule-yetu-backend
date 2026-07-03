<?php

namespace App\K1\Ai\Admin;

use App\Modules\ShuleYetu\Models\ShuleExamSubject;
use Illuminate\Support\Facades\DB;

/**
 * StaffPerformanceAnalyzer — Ranks teachers based on classroom outcomes.
 *
 * @package App\K1\Ai\Admin
 */
class StaffPerformanceAnalyzer
{
    /**
     * Analyze teacher/ staff performance.
     *
     * @param string $schoolId
     * @return array{top_teachers: array, avg_score: float}
     */
    public function analyze(string $schoolId): array
    {
        $feedback = DB::table('k1_teacher_feedback')
            ->where('school_id', $schoolId)
            ->select('teacher_id')
            ->selectRaw('
                AVG(student_understanding) as avg_understanding,
                COUNT(*) as total_feedback
            ')
            ->groupBy('teacher_id')
            ->orderByDesc('avg_understanding')
            ->get()
            ->toArray();

        return [
            'top_teachers' => $feedback,
            'avg_score'   => round(collect($feedback)->avg('avg_understanding') ?? 0, 2),
        ];
    }
}