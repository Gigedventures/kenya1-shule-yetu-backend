<?php

namespace App\K1\Ai\Services;

use App\Modules\ShuleYetu\Models\ShuleStudent;
use App\Modules\ShuleYetu\Models\ShuleAttendance;
use App\Modules\ShuleYetu\Models\ShuleStudentBill;
use App\Modules\ShuleYetu\Models\ShuleTermResult;
use App\Modules\ShuleYetu\Support\Tenancy\SchoolContext;
use Illuminate\Support\Facades\DB;

class AtRiskDetector
{
    /**
     * Detect whether a student is at risk of falling behind or dropping out.
     *
     * @return array{
     *     risk_score: int,
     *     risk_level: string,
     *     reasons: string[],
     *     recommended_actions: string[],
     * }
     */
    public function detect(string $studentId): array
    {
        $schoolId = app(SchoolContext::class)->requireId();

        $student = ShuleStudent::query()
            ->where('school_id', $schoolId)
            ->findOrFail($studentId);

        $reasons  = [];
        $actions  = [];
        $score    = 0;
        $maxScore = 100;

        // -- 1. Attendance Decline (35 points max) --
        $recentAttendance = ShuleAttendance::query()
            ->where('student_id', $student->id)
            ->where('school_id', $schoolId)
            ->where('created_at', '>=', now()->subDays(30))
            ->selectRaw('
                COALESCE(
                    SUM(CASE WHEN status = \'present\' THEN 1 ELSE 0 END) * 100.0 /
                    NULLIF(COUNT(*), 0),
                    0
                ) as pct
            ')
            ->value('pct');

        if ($recentAttendance < 80) {
            $score += 35;
            $reasons[] = 'Attendance dropped below 80% in last 30 days';
            $actions[] = 'Send attendance alert to parent/guardian';
            $actions[] = 'Schedule check-in with student counsellor';
        }

        // -- 2. Unpaid Fees (25 points max) --
        $outstanding = ShuleStudentBill::query()
            ->where('student_id', $student->id)
            ->where('school_id', $schoolId)
            ->where('status', 'unpaid')
            ->sum('balance');

        if ($outstanding > 0) {
            $score += min(25, (int) ($outstanding / 1000));
            if ($outstanding > 5000) {
                $reasons[] = "Outstanding fee balance of KES {$outstanding}";
                $actions[] = 'Initiate fee collection reminder';
                $actions[] = 'Offer flexible payment plan';
            }
        }

        // -- 3. Grade Decline (40 points max) --
        $recentAvg = ShuleTermResult::query()
            ->where('student_id', $student->id)
            ->where('school_id', $schoolId)
            ->orderByDesc('created_at')
            ->value('average') ?? 50;

        if ($recentAvg < 40) {
            $score += 40;
            $reasons[] = 'Average grade dropped below 40%';
            $actions[] = 'Assign remedial tutoring session';
            $actions[] = 'Notify subject teachers for intervention';
        } elseif ($recentAvg < 60) {
            $score += 20;
            $reasons[] = 'Average grade between 40-60% — needs improvement';
            $actions[] = 'Recommend extra practice materials';
        }

        $riskLevel = match (true) {
            $score >= 70 => 'high',
            $score >= 40 => 'medium',
            default      => 'low',
        };

        return [
            'risk_score'         => min($score, $maxScore),
            'risk_level'         => $riskLevel,
            'reasons'            => $reasons,
            'recommended_actions' => $actions,
        ];
    }
}