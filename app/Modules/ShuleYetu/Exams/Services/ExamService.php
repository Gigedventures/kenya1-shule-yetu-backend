<?php

namespace App\Modules\ShuleYetu\Exams\Services;

use App\Models\User;
use App\Modules\ShuleYetu\Models\ShuleExam;
use App\Modules\ShuleYetu\Models\ShuleExamScore;
use App\Modules\ShuleYetu\Models\ShuleExamSubject;
use App\Modules\ShuleYetu\Models\ShuleGradeBand;
use App\Modules\ShuleYetu\Models\ShuleTermResult;
use App\Modules\ShuleYetu\Support\Tenancy\SchoolContext;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class ExamService
{
    public function createExam(array $data): ShuleExam
    {
        $schoolId = app(SchoolContext::class)->requireId();

        $academicYear = $this->requireSameSchool('shule_academic_years', $data['academic_year_id'] ?? null, $schoolId, 'Academic year');
        $term = $this->requireSameSchool('shule_terms', $data['term_id'] ?? null, $schoolId, 'Term', ['academic_year_id']);
        $examType = $this->requireSameSchool('shule_exam_types', $data['exam_type_id'] ?? null, $schoolId, 'Exam type');
        $class = $this->requireSameSchool('shule_classes', $data['class_id'] ?? null, $schoolId, 'Class');

        if ($term->academic_year_id !== $academicYear->id) {
            throw new RuntimeException('Term must belong to selected academic year.');
        }

        $streamId = $data['stream_id'] ?? null;
        if ($streamId) {
            $stream = $this->requireSameSchool('shule_streams', $streamId, $schoolId, 'Stream', ['class_id']);
            if ($stream->class_id !== $class->id) {
                throw new RuntimeException('Stream must belong to selected class.');
            }
        }

        $exists = ShuleExam::query()
            ->where('term_id', $term->id)
            ->where('exam_type_id', $examType->id)
            ->where('class_id', $class->id)
            ->when($streamId, fn ($q) => $q->where('stream_id', $streamId), fn ($q) => $q->whereNull('stream_id'))
            ->where('title', $data['title'] ?? '')
            ->exists();

        if ($exists) {
            throw new RuntimeException('Exam with same title already exists for this class and term.');
        }

        $data['status'] = 'draft';

        return ShuleExam::query()->create($data);
    }

    public function addSubjectToExam(string $examId, string $subjectId, int $maxMarks, ?int $passMark = null): ShuleExamSubject
    {
        $schoolId = app(SchoolContext::class)->requireId();

        $exam = ShuleExam::query()->findOrFail($examId);
        if ($exam->status !== 'draft') {
            throw new RuntimeException('Subjects can only be added to draft exams.');
        }

        $this->requireSameSchool('shule_exams', $exam->id, $schoolId, 'Exam');
        $subject = $this->requireSameSchool('shule_subjects', $subjectId, $schoolId, 'Subject');

        $allowed = DB::table('shule_class_subject')
            ->where('school_id', $schoolId)
            ->where('class_id', $exam->class_id)
            ->where('subject_id', $subject->id)
            ->exists();

        if (!$allowed) {
            throw new RuntimeException('Selected subject is not allowed for this class.');
        }

        return ShuleExamSubject::query()->create([
            'exam_id' => $exam->id,
            'subject_id' => $subject->id,
            'max_marks' => $maxMarks,
            'pass_mark' => $passMark,
        ]);
    }

    public function publishExam(string $examId): ShuleExam
    {
        $exam = ShuleExam::query()->findOrFail($examId);
        if ($exam->status !== 'draft') {
            throw new RuntimeException('Only draft exams can be published.');
        }

        $subjectCount = ShuleExamSubject::query()->where('exam_id', $exam->id)->count();
        if ($subjectCount < 1) {
            throw new RuntimeException('Cannot publish exam without subjects.');
        }

        $exam->status = 'published';
        $exam->save();

        return $exam;
    }

    public function closeExam(string $examId): ShuleExam
    {
        $exam = ShuleExam::query()->findOrFail($examId);
        if ($exam->status !== 'published') {
            throw new RuntimeException('Only published exams can be closed.');
        }

        $exam->status = 'closed';
        $exam->save();

        return $exam;
    }

    public function enterMarksBulk(string $examSubjectId, array $marksArray, User $actorUser): void
    {
        $schoolId = app(SchoolContext::class)->requireId();

        if (!$actorUser->hasPermission('exams.score') && !$actorUser->hasPermission('exams.manage')) {
            throw new RuntimeException('User does not have permission to enter exam scores.');
        }

        $examSubject = ShuleExamSubject::query()->with('exam')->findOrFail($examSubjectId);
        if ($examSubject->exam->status !== 'published') {
            throw new RuntimeException('Marks can only be entered for published exams.');
        }

        $exam = $examSubject->exam;
        $maxMarks = (float) $examSubject->max_marks;

        DB::transaction(function () use ($marksArray, $examSubject, $exam, $maxMarks, $actorUser, $schoolId): void {
            foreach ($marksArray as $entry) {
                $studentId = $entry['student_id'] ?? null;
                $marks = $entry['marks_obtained'] ?? null;

                if ($studentId === null || $marks === null) {
                    throw new RuntimeException('Student and marks are required for score entry.');
                }

                $student = $this->requireSameSchool('shule_students', $studentId, $schoolId, 'Student', ['current_class_id', 'current_stream_id']);

                if ((string) $student->current_class_id !== (string) $exam->class_id) {
                    throw new RuntimeException('Student does not belong to exam class.');
                }

                if ($exam->stream_id && (string) $student->current_stream_id !== (string) $exam->stream_id) {
                    throw new RuntimeException('Student does not belong to exam stream.');
                }

                $marksValue = (float) $marks;
                if ($marksValue < 0 || $marksValue > $maxMarks) {
                    throw new RuntimeException('Marks must be within subject maximum.');
                }

                $percentage = $maxMarks > 0 ? round(($marksValue / $maxMarks) * 100, 2) : 0.0;
                $grade = $this->resolveGrade($schoolId, $percentage);

                ShuleExamScore::query()->updateOrCreate(
                    [
                        'exam_subject_id' => $examSubject->id,
                        'student_id' => $studentId,
                    ],
                    [
                        'marks_obtained' => $marksValue,
                        'percentage' => $percentage,
                        'grade' => $grade,
                        'remarks' => $entry['remarks'] ?? null,
                        'entered_by_user_id' => $actorUser->getKey(),
                    ]
                );
            }
        });
    }

    public function calculateTermResults(string $termId, array $options = []): void
    {
        $schoolId = app(SchoolContext::class)->requireId();
        $term = $this->requireSameSchool('shule_terms', $termId, $schoolId, 'Term', ['academic_year_id']);
        $academicYearId = (string) $term->academic_year_id;
        $rankingEnabled = (bool) ($options['ranking_enabled'] ?? false);

        $exams = ShuleExam::query()
            ->where('term_id', $termId)
            ->where('status', 'closed')
            ->with(['examType', 'subjects'])
            ->get();

        if ($exams->isEmpty()) {
            throw new RuntimeException('No closed exams found for the term.');
        }

        $students = DB::table('shule_students')
            ->where('school_id', $schoolId)
            ->select(['id'])
            ->get();

        DB::transaction(function () use ($students, $exams, $schoolId, $academicYearId, $termId, $rankingEnabled): void {
            foreach ($students as $student) {
                $totalMarks = 0.0;
                $weightedSum = 0.0;
                $weightTotal = 0.0;

                foreach ($exams as $exam) {
                    $subjectIds = $exam->subjects->pluck('id')->all();
                    if (empty($subjectIds)) {
                        continue;
                    }

                    $subjectScores = DB::table('shule_exam_scores')
                        ->where('student_id', $student->id)
                        ->whereIn('exam_subject_id', $subjectIds)
                        ->selectRaw('SUM(marks_obtained) as total_marks')
                        ->first();

                    $marks = (float) ($subjectScores->total_marks ?? 0);
                    $maxMarks = (float) $exam->subjects->sum('max_marks');

                    if ($maxMarks <= 0) {
                        continue;
                    }

                    $examPercentage = round(($marks / $maxMarks) * 100, 2);
                    $weight = (float) ($exam->examType?->weight ?? 0);
                    $weightedSum += $examPercentage * $weight;
                    $weightTotal += $weight;
                    $totalMarks += $marks;
                }

                if ($weightTotal <= 0) {
                    continue;
                }

                $totalPercentage = round($weightedSum / $weightTotal, 2);
                $overallGrade = $this->resolveGrade($schoolId, $totalPercentage);

                ShuleTermResult::query()->updateOrCreate(
                    [
                        'term_id' => $termId,
                        'student_id' => $student->id,
                    ],
                    [
                        'academic_year_id' => $academicYearId,
                        'total_marks' => $totalMarks,
                        'total_percentage' => $totalPercentage,
                        'average' => $totalPercentage,
                        'overall_grade' => $overallGrade,
                        'rank' => null,
                    ]
                );
            }

            if ($rankingEnabled) {
                $ranked = ShuleTermResult::query()
                    ->where('term_id', $termId)
                    ->orderByDesc('average')
                    ->orderBy('student_id')
                    ->get();

                $rank = 1;
                foreach ($ranked as $result) {
                    $result->rank = $rank++;
                    $result->save();
                }
            }
        });
    }

    public function buildStudentTermReport(string $studentId, string $termId): array
    {
        $schoolId = app(SchoolContext::class)->requireId();
        $student = $this->requireSameSchool('shule_students', $studentId, $schoolId, 'Student');
        $term = $this->requireSameSchool('shule_terms', $termId, $schoolId, 'Term', ['academic_year_id']);

        $exams = ShuleExam::query()
            ->where('term_id', $termId)
            ->where('status', 'closed')
            ->with(['examType', 'subjects.subject'])
            ->get();

        $examSubjectIds = $exams->flatMap(fn ($exam) => $exam->subjects->pluck('id'))
            ->filter()
            ->unique()
            ->values()
            ->all();

        $scores = ShuleExamScore::query()
            ->where('student_id', $student->id)
            ->whereIn('exam_subject_id', $examSubjectIds)
            ->get()
            ->keyBy(fn ($score) => $score->exam_subject_id);

        $examBreakdown = $exams->map(function ($exam) use ($scores) {
            $subjects = $exam->subjects->map(function ($subject) use ($scores) {
                $score = $scores->get($subject->id);
                return [
                    'subject_id' => $subject->subject_id,
                    'subject_name' => $subject->subject?->name,
                    'max_marks' => $subject->max_marks,
                    'marks_obtained' => $score?->marks_obtained,
                    'percentage' => $score?->percentage,
                    'grade' => $score?->grade,
                    'remarks' => $score?->remarks,
                ];
            });

            return [
                'exam_id' => $exam->id,
                'title' => $exam->title,
                'exam_type' => $exam->examType?->name,
                'subjects' => $subjects,
            ];
        });

        $termResult = ShuleTermResult::query()
            ->where('term_id', $termId)
            ->where('student_id', $student->id)
            ->first();

        return [
            'student_id' => $student->id,
            'term_id' => $term->id,
            'academic_year_id' => $term->academic_year_id,
            'exams' => $examBreakdown,
            'term_result' => $termResult,
        ];
    }

    private function resolveGrade(string $schoolId, float $percentage): ?string
    {
        $band = ShuleGradeBand::query()
            ->where('school_id', $schoolId)
            ->where('min_percentage', '<=', $percentage)
            ->where('max_percentage', '>=', $percentage)
            ->orderBy('min_percentage', 'desc')
            ->first();

        return $band?->grade;
    }

    private function requireSameSchool(
        string $table,
        ?string $id,
        string $schoolId,
        string $label,
        array $columns = []
    ) {
        if (!$id) {
            throw new RuntimeException("{$label} is required.");
        }

        $record = DB::table($table)
            ->where('id', $id)
            ->when(!empty($columns), fn ($q) => $q->select(array_merge(['id', 'school_id'], $columns)))
            ->first();

        if (!$record || $record->school_id !== $schoolId) {
            throw new RuntimeException("{$label} must belong to active school.");
        }

        return $record;
    }
}
