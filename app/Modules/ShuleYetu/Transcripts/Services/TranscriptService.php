<?php

namespace App\Modules\ShuleYetu\Transcripts\Services;

use App\Modules\ShuleYetu\Models\ShuleStudent;
use App\Modules\ShuleYetu\Models\ShuleTermResult;
use App\Modules\ShuleYetu\Support\Tenancy\SchoolContext;
use App\Modules\ShuleYetu\Models\ShuleExam;
use App\Modules\ShuleYetu\Models\ShuleExamScore;
use App\Modules\ShuleYetu\Models\ShuleExamSubject;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use RuntimeException;

class TranscriptService
{
    /**
     * Build a comprehensive academic transcript for a student.
     *
     * @throws RuntimeException
     */
    public function buildTranscript(string $studentId): array
    {
        $schoolId = app(SchoolContext::class)->requireId();

        $student = ShuleStudent::query()
            ->where('school_id', $schoolId)
            ->findOrFail($studentId);

        // Get all completed term results (closed exams that produced results)
        $termResults = ShuleTermResult::query()
            ->where('student_id', $student->id)
            ->with(['term'])
            ->orderByDesc('created_at')
            ->get();

        if ($termResults->isEmpty()) {
            throw new RuntimeException('No completed term results found for this student.');
        }

        // Build term-by-term breakdown
        $terms = $termResults->map(function (ShuleTermResult $result) use ($schoolId, $student) {
            $term = $result->term;
            $exams = ShuleExam::query()
                ->where('term_id', $result->term_id)
                ->whereHas('subjects', function ($q) use ($student) {
                    $q->whereHas('scores', function ($sq) use ($student) {
                        $sq->where('student_id', $student->id);
                    });
                })
                ->with(['subjects' => function ($q) use ($student) {
                    $q->with(['scores' => function ($sq) use ($student) {
                        $sq->where('student_id', $student->id);
                    }, 'subject']);
                }])
                ->get();

            return [
                'term' => [
                    'id' => $result->term_id,
                    'name' => $term?->name ?? 'Term',
                    'academic_year_id' => $result->academic_year_id,
                ],
                'total_marks' => (float) $result->total_marks,
                'total_percentage' => (float) $result->total_percentage,
                'average' => (float) $result->average,
                'overall_grade' => $result->overall_grade,
                'rank' => $result->rank,
            ];
        });

        // Calculate cumulative statistics
        $totalTerms = $terms->count();
        $sumOfAverages = $terms->sum('average');
        $cumulativeAverage = $totalTerms > 0 ? round($sumOfAverages / $totalTerms, 2) : 0.0;

        return [
            'student' => [
                'id' => $student->id,
                'name' => trim("{$student->first_name} {$student->last_name}"),
                'admission_no' => $student->admission_no,
                'current_class_id' => $student->current_class_id,
            ],
            'terms' => $terms->values()->toArray(),
            'cumulative' => [
                'total_terms' => $totalTerms,
                'average' => $cumulativeAverage,
                'highest_grade' => $termResults->sortByDesc('average')->first()?->overall_grade,
            ],
        ];
    }
}