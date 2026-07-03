<?php

namespace App\K1\Ai\Services;

use App\Modules\ShuleYetu\Models\ShuleStudent;
use App\Modules\ShuleYetu\Models\ShuleExamScore;
use App\Modules\ShuleYetu\Models\ShuleTermResult;
use App\Modules\ShuleYetu\Models\ShuleAttendance;
use App\Modules\ShuleYetu\Support\Tenancy\SchoolContext;
use Illuminate\Support\Facades\DB;

class StudentPerformancePredictor
{
    /**
     * Predict a student's future performance based on historical data.
     *
     * @return array{
     *     predicted_average: float,
     *     risk_level: string,
     *     strongest_subjects: array,
     *     weakest_subjects: array,
     *     confidence_score: float,
     * }
     */
    public function predict(string $studentId, ?string $schoolId = null): array
    {
        $schoolId ??= app(SchoolContext::class)->requireId();

        $student = ShuleStudent::query()
            ->where('school_id', $schoolId)
            ->findOrFail($studentId);

        // -- Exam Score Average (weighted 0.5) --
        $examScores = ShuleExamScore::query()
            ->where('student_id', $student->id)
            ->where('school_id', $schoolId)
            ->selectRaw('AVG(CAST(percentage AS DECIMAL(10,2))) as avg_percentage')
            ->value('avg_percentage') ?? 0.0;

        // -- Term Result Average (weighted 0.3) --
        $termResults = ShuleTermResult::query()
            ->where('student_id', $student->id)
            ->avg('average') ?? 0.0;

        // -- Attendance Rate (weighted 0.2) --
        $attendance = ShuleAttendance::query()
            ->where('student_id', $student->id)
            ->where('school_id', $schoolId)
            ->selectRaw('
                COALESCE(
                    SUM(CASE WHEN status = \'present\' THEN 1 ELSE 0 END) * 100.0 /
                    NULLIF(COUNT(*), 0),
                    0
                ) as pct
            ')
            ->value('pct') ?? 0.0;

        // -- Weighted Prediction --
        $predictedAverage = round(
            ($examScores * 0.5) + ($termResults * 0.3) + ($attendance * 0.2),
            2
        );

        // -- Risk Level --
        $riskLevel = match (true) {
            $predictedAverage >= 75 => 'low',
            $predictedAverage >= 50 => 'medium',
            default              => 'high',
        };

        // -- Subject Strength/Weakness by percentile --
        $subjectScores = DB::table('shule_exam_scores')
            ->where('student_id', $student->id)
            ->where('school_id', $schoolId)
            ->join('shule_exam_subjects', 'shule_exam_scores.exam_subject_id', '=', 'shule_exam_subjects.id')
            ->join('shule_subjects', 'shule_exam_subjects.subject_id', '=', 'shule_subjects.id')
            ->selectRaw('
                shule_subjects.name,
                AVG(shule_exam_scores.percentage) as avg_pct
            ')
            ->groupBy('shule_subjects.id', 'shule_subjects.name')
            ->orderByDesc('avg_pct')
            ->get();

        $scores = $subjectScores->pluck('avg_pct', 'name');

        $cutoff = $scores->avg() ?? 50;

        $strongest = $scores->filter(fn ($v) => $v >= $cutoff)->sortDesc()->keys()->take(3)->values()->toArray();
        $weakest  = $scores->filter(fn ($v) => $v <  $cutoff)->sortAsc()->keys()->take(3)->values()->toArray();

        return [
            'predicted_average'  => $predictedAverage,
            'risk_level'         => $riskLevel,
            'strongest_subjects' => $strongest,
            'weakest_subjects'   => $weakest,
            'confidence_score'   => round(min(100, $predictedAverage * 0.7 + 30), 2),
        ];
    }
}