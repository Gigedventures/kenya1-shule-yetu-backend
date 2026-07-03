<?php

namespace App\K1\Ai\Admin;

use App\Modules\ShuleYetu\Models\ShuleSubject;
use Illuminate\Support\Facades\DB;

/**
 * ResourceUtilizationTracker — Tracks how resources (teachers, aids, materials) are used.
 *
 * @package App\K1\Ai\Admin
 */
class ResourceUtilizationTracker
{
    /**
     * Track resource usage.
     *
     * @param string $schoolId
     * @return array{utilization: float, subjects: array}
     */
    public function track(string $schoolId): array
    {
        $subjects = DB::table('shule_subjects')
            ->where('school_id', $schoolId)
            ->get()
            ->count();
        $teachers = DB::table('k1_teacher_feedback')
            ->where('school_id', $schoolId)
            ->distinct('teacher_id')
            ->count('teacher_id');

        return [
            'utilization' => $teachers > 0 ? round($subjects / $teachers, 2) : 0,
            'subjects'   => $subjects,
        ];
    }
}