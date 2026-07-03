<?php

namespace App\K1\Ai\Services;

use App\Modules\ShuleYetu\Models\ShuleStudent;
use App\Modules\ShuleYetu\Models\ShuleExamScore;
use App\Modules\ShuleYetu\Models\ShuleTermResult;
use App\Modules\ShuleYetu\Support\Tenancy\SchoolContext;
use Illuminate\Support\Facades\DB;

class LearningPlanGenerator
{
    /**
     * Generate a 14-day personalized learning plan.
     *
     * @param array $weaknesses   List of subject names where the student is weak
     * @param array $strengths    List of subject names where the student is strong
     * @param array $gaps         Competency gaps from CompetencyGapAnalyzer
     * @return array{ plan: array, total_sessions: int, estimated_hours: float }
     */
    public function generate(
        array $weaknesses,
        array $strengths,
        array $gaps = []
    ): array {
        $days     = [];
        $sessions = 0;
        $hours    = 0;

        // -- Daily schedule: alternating weak + strong subjects --
        $allSubjects = array_unique(array_merge($weaknesses, $strengths));
        $focusSubjects = array_slice($weaknesses, 0, 4);
        $maintainSubjects = array_slice($strengths, 0, 2);

        $schedulePatterns = [
            ['focus' => 2, 'maintain' => 1],   // Day 1-2
            ['focus' => 3, 'maintain' => 0],   // Day 3-4
            ['focus' => 1, 'maintain' => 2],   // Day 5-6
            ['focus' => 2, 'maintain' => 1],   // Day 7-8
            ['focus' => 1, 'maintain' => 1],   // Day 9-10
            ['focus' => 2, 'maintain' => 0],   // Day 11-12
            ['focus' => 1, 'maintain' => 1],   // Day 13-14
        ];

        for ($day = 1; $day <= 14; $day++) {
            $pattern = $schedulePatterns[($day - 1) % count($schedulePatterns)];

            $todayFocus = array_slice($focusSubjects, 0, $pattern['focus']);
            $todayMaint = array_slice($maintainSubjects, 0, $pattern['maintain']);

            $daySubjects = array_merge($todayFocus, $todayMaint);

            $daySessions = [
                'day'    => $day,
                'focus'  => $todayFocus,
                'review' => $todayMaint,
                'hours'  => count($daySubjects) * 1.5,
            ];

            $days[] = $daySessions;
            $sessions += count($daySubjects);
            $hours    += $daySessions['hours'];
        }

        return [
            'plan'            => $days,
            'total_sessions' => $sessions,
            'estimated_hours' => round($hours, 1),
        ];
    }
}