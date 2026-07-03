<?php

namespace App\K1\Ai\TeacherPortal\Assessments;

use App\K1\Ai\Core\Curriculum\CBCMapper;
use App\Modules\ShuleYetu\Support\Tenancy\SchoolContext;
use Illuminate\Support\Facades\DB;

class AssessmentService
{
    private CBCMapper $cbc;

    public function __construct() { $this->cbc = app(CBCMapper::class); }

    // 1. AI question generator
    public function generateQuestions(string $subject, string $topic, int $count = 5): array
    {
        $band = $this->cbc->resolveCompetency(70.0);
        $questions = [];
        for ($i = 1; $i <= $count; $i++) {
            $questions[] = [
                'id' => "q{$i}", 'text' => "Explain {$topic} using {$band} framework",
                'type' => 'essay', 'max_marks' => 10,
            ];
        }
        return ['subject' => $subject, 'topic' => $topic, 'questions' => $questions];
    }

    // 2. Exam paper builder
    public function buildPaper(string $subject, string $term): array
    {
        $topics = DB::table('k1_schemes')->where('subject', $subject)->get()->toArray();
        return ['subject' => $subject, 'term' => $term, 'sections' => $topics];
    }

    // 3. Automated grading
    public function autoGrade(string $examId): array
    {
        $scores = DB::table('k1_exam_scores')->where('exam_id', $examId)->get()->toArray();
        $graded = [];
        foreach ($scores as $s) {
            $graded[] = [
                'student_id' => $s->student_id,
                'score' => (float)$s->marks_obtained,
                'grade' => $this->cbc->resolveCompetency((float)$s->marks_obtained),
            ];
        }
        return ['exam_id' => $examId, 'results' => $graded];
    }

    // 4. Rubric scoring (extend Sprint 2)
    public function rubricScore(string $subject, string $task): array
    {
        return [
            'subject' => $subject, 'task' => $task,
            'criteria' => [
                ['name' => 'Accuracy', 'weight' => 40, 'levels' => ['Excellent', 'Good', 'Fair', 'Needs Improvement']],
                ['name' => 'Application', 'weight' => 30, 'levels' => ['Excellent', 'Good', 'Fair', 'Needs Improvement']],
                ['name' => 'Presentation', 'weight' => 30, 'levels' => ['Excellent', 'Good', 'Fair', 'Needs Improvement']],
            ],
        ];
    }

    // 5. Continuous assessment
    public function trackCA(string $studentId): array
    {
        $t = DB::table('k1_lesson_outcomes')->where('student_id', $studentId)->sum('effectiveness');
        return ['student_id' => $studentId, 'ca_score' => round($t / 100, 2)];
    }

    // 6. Exam timetable
    public function generateTimetable(string $term): array
    {
        return ['term' => $term, 'schedule' => [['date' => now()->addDays(7)->toDateString(), 'subject' => 'Mathematics']]];
    }
}