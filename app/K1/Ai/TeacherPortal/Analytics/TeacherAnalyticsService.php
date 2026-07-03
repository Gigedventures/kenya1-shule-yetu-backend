<?php

namespace App\K1\Ai\TeacherPortal\Analytics;

use App\Modules\ShuleYetu\Support\Tenancy\SchoolContext;
use App\K1\Ai\Core\LearningLoop\AdaptiveWeightsEngine;
use Illuminate\Support\Facades\DB;

class TeacherAnalyticsService
{
    public function performance(string $teacherId): array
    {
        $schoolId = app(SchoolContext::class)->requireId();
        $feedback = DB::table('k1_teacher_feedback')->where('teacher_id', $teacherId)->avg('student_understanding') ?? 0;
        $lessons = DB::table('k1_lesson_outcomes')->where('teacher_id', $teacherId)->count();
        $outcomes = DB::table('k1_lesson_outcomes')->where('teacher_id', $teacherId)->avg('effectiveness') ?? 0;
        return [
            'teacher_id' => $teacherId, 'avg_understanding' => round($feedback, 2),
            'lessons' => $lessons, 'effectiveness' => round($outcomes, 2),
            'score' => round(($feedback + $outcomes) / 2, 2),
        ];
    }

    public function impact(string $teacherId): array
    {
        $scores = DB::table('k1_lesson_outcomes')->where('teacher_id', $teacherId)->get()->toArray();
        $improvement = collect($scores)->avg('effectiveness') ?? 0;
        return ['teacher_id' => $teacherId, 'impact_score' => round($improvement, 2)];
    }
}