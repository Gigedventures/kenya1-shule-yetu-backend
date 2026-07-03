<?php

namespace App\K1\Ai\Student\Competency;

use App\K1\Ai\Core\Curriculum\CBCMapper;
use App\Modules\ShuleYetu\Support\Tenancy\SchoolContext;

class CompetencyTrackerService
{
    public function track(string $studentId): array
    {
        $schoolId = app(SchoolContext::class)->requireId();
        $subjects = DB::table('k1_lesson_outcomes')
            ->where('student_id', $studentId)
            ->select('subject', DB::raw('AVG(effectiveness) as avg'))
            ->groupBy('subject')
            ->get()
            ->toArray();

        return [
            'subjects' => $subjects,
            'radar' => [
                'labels' => array_column($subjects, 'subject'),
                'values' => array_column($subjects, 'avg'),
            ],
        ];
    }

    public function getHeatmap(): array
    {
        $schoolId = app(SchoolContext::class)->requireId();
        $results = DB::table('k1_lesson_outcomes')
            ->where('school_id', $schoolId)
            ->select('subject')
            ->selectRaw('AVG(effectiveness) as avg')
            ->groupBy('subject')
            ->get()
            ->toArray();

        return ['data' => $results];
    }
}