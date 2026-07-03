<?php

namespace App\K1\Ai\Shared;

use App\K1\Ai\National\SchoolBenchmarkEngine;
use App\K1\Ai\Admin\SchoolPerformanceDashboard;
use Illuminate\Support\Facades\DB;

/**
 * RankingEngine — Generates transparent, explainable rankings.
 *
 * Rankings are:
 *   - school rankings
 *   - subject rankings per region
 *   - teacher performance indexes (aggregated)
 *
 * All rankings must be explainable — not black-box scores.
 *
 * @package App\K1\Ai\Shared
 */
class RankingEngine
{
    private SchoolBenchmarkEngine $benchmark;
    private SchoolPerformanceDashboard $admin;

    public function __construct()
    {
        $this->benchmark = app(SchoolBenchmarkEngine::class);
        $this->admin = app(SchoolPerformanceDashboard::class);
    }

    /**
     * Generate a complete set of rankings.
     *
     * @return array{schools: array, subjects: array, teachers: array}
     */
    public function rank(): array
    {
        $schools = $this->benchmark->benchmark();
        $subjects = DB::table('k1_lesson_outcomes')
            ->select('subject')
            ->selectRaw('AVG(effectiveness) as avg')
            ->groupBy('subject')
            ->orderByDesc('avg')
            ->get()
            ->toArray();
        $teachers = DB::table('k1_teacher_feedback')
            ->select('teacher_id')
            ->selectRaw('AVG(student_understanding) as avg')
            ->groupBy('teacher_id')
            ->orderByDesc('avg')
            ->get()
            ->toArray();

        return [
            'schools'  => $schools,
            'subjects' => $subjects,
            'teachers' => $teachers,
        ];
    }
}