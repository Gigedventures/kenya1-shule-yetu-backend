<?php

namespace App\K1\Ai\Services;

use App\Modules\ShuleYetu\Models\ShuleStudent;
use App\Modules\ShuleYetu\Models\ShuleExamScore;
use App\Modules\ShuleYetu\Models\ShuleExamSubject;
use App\Modules\ShuleYetu\Models\ShuleSubject;
use App\Modules\ShuleYetu\Support\Tenancy\SchoolContext;
use Illuminate\Support\Facades\DB;

class CompetencyGapAnalyzer
{
    /**
     * Analyze CBC competency gaps — where a student falls short of expected targets.
     *
     * @return array{
     *     competency_gaps: array,
     *     strengths: string[],
     *     interventions: string[],
     * }
     */
    public function analyze(string $studentId): array
    {
        $schoolId = app(SchoolContext::class)->requireId();

        $student = ShuleStudent::query()
            ->where('school_id', $schoolId)
            ->findOrFail($studentId);

        // Expected target = 70% for core competencies
        $expectedTarget = 70.0;

        // -- Per-subject averages from exam scores --
        $subjectScores = DB::table('shule_exam_scores')
            ->where('student_id', $student->id)
            ->where('school_id', $schoolId)
            ->join('shule_exam_subjects', 'shule_exam_scores.exam_subject_id', '=', 'shule_exam_subjects.id')
            ->join('shule_subjects', 'shule_exam_subjects.subject_id', '=', 'shule_subjects.id')
            ->selectRaw('
                shule_subjects.id,
                shule_subjects.name,
                AVG(shule_exam_scores.percentage) as avg_pct,
                COUNT(shule_exam_scores.id) as attempts
            ')
            ->groupBy('shule_subjects.id', 'shule_subjects.name')
            ->having('attempts', '>=', 2)
            ->get();

        $gaps     = [];
        $strengths = [];
        $interventions = [];

        foreach ($subjectScores as $row) {
            $avg = (float) $row->avg_pct;
            $gap = round($expectedTarget - $avg, 2);

            if ($gap > 10) {
                $gaps[] = [
                    'subject'           => $row->name,
                    'current_average'    => $avg,
                    'expected'          => $expectedTarget,
                    'gap'               => $gap,
                    'severity'          => $gap > 20 ? 'critical' : 'moderate',
                ];
                $interventions[] = match (true) {
                    $gap > 20 => "Intensive {$row->name} revision required — 4+ extra sessions/week",
                    $gap > 10 => "Supplemental {$row->name} practice — 2 extra sessions/week",
                    default   => "Monitor {$row->name} progress — maintain current pace",
                };
            } else {
                $strengths[] = $row->name;
            }
        }

        return [
            'competency_gaps' => $gaps,
            'strengths'       => array_slice($strengths, 0, 5),
            'interventions'   => $interventions,
        ];
    }
}