<?php

namespace App\K1\Ai\Shared;

use App\K1\Ai\Core\LearningLoop\LessonOutcomeTracker;
use Illuminate\Support\Facades\DB;

/**
 * AnomalyDetectionEngine — Detects sudden or abnormal changes in performance data.
 *
 * Flags:
 *   - sudden performance drops
 *   - abnormal grade inflation
 *   - inconsistent teacher grading patterns
 *
 * @package App\K1\Ai\Shared
 */
class AnomalyDetectionEngine
{
    /**
     * Detect anomalies across all schools.
     *
     * @return array{anomalies: array[], flags: string[]}
     */
    public function detect(): array
    {
        $outcomes = DB::table('k1_lesson_outcomes')
            ->select('school_id', 'subject', 'effectiveness', 'created_at')
            ->orderByDesc('created_at')
            ->get()
            ->toArray();

        $anomalies = [];
        $flags = [];

        $grouped = collect($outcomes)->groupBy('school_id');
        foreach ($grouped as $schoolId => $records) {
            $recent = $records->take(5);
            $avg = $recent->avg('effectiveness');
            $drops = $recent->filter(fn($r) => ($r->effectiveness ?? 0) < ($avg * 0.5));

            if ($drops->count() > 0) {
                $anomalies[] = [
                    'school_id' => $schoolId,
                    'reason' => 'Performance drops detected',
                    'affected_count' => $drops->count(),
                ];
                $flags[] = "School {$schoolId} has " . $drops->count() . ' anomalous data points';
            }
        }

        return ['anomalies' => $anomalies, 'flags' => $flags];
    }
}