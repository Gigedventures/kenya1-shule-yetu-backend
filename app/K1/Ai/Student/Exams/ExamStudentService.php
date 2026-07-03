<?php

namespace App\K1\Ai\Student\Exams;

use App\K1\Ai\Services\StudentPerformancePredictor;
use App\Modules\ShuleYetu\Models\ShuleExamScore;
use App\Modules\ShuleYetu\Models\ShuleTermResult;
use App\Modules\ShuleYetu\Support\Tenancy\SchoolContext;

class ExamStudentService
{
    private StudentPerformancePredictor $predictor;

    public function __construct()
    {
        $this->predictor = app(StudentPerformancePredictor::class);
    }

    public function getSchedule(string $gradeId): array
    {
        return DB::table('shule_exams')
            ->where('class_id', $gradeId)
            ->orderBy('start_date')
            ->get()
            ->toArray();
    }

    public function getResults(string $studentId): array
    {
        $results = ShuleTermResult::query()
            ->where('student_id', $studentId)
            ->orderByDesc('created_at')
            ->get()
            ->toArray();

        $scores = ShuleExamScore::query()
            ->where('student_id', $studentId)
            ->get()
            ->toArray();

        $predictions = $this->predictor->predict($studentId);

        return [
            'term_results' => $results,
            'exam_scores' => $scores,
            'predicted'   => $predictions,
            'trend'       => collect($results)->avg('average'),
        ];
    }

    public function getReportCard(string $studentId): array
    {
        $results = ShuleTermResult::query()
            ->where('student_id', $studentId)
            ->orderByDesc('created_at')
            ->first();

        return [
            'student_id' => $studentId,
            'average'    => (float) ($results->average ?? 0),
            'grade'      => (string) ($results->overall_grade ?? ''),
            'rank'       => (int) ($results->rank ?? 0),
        ];
    }
}