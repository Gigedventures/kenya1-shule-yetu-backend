<?php

namespace App\K1\Ai\Services\Teacher;

use App\K1\Ai\Core\Curriculum\CBCMapper;
use App\Modules\ShuleYetu\Support\Tenancy\SchoolContext;
use RuntimeException;

class SchemeOfWorkGenerator
{
    private CBCMapper $cbc;

    public function __construct()
    {
        $this->cbc = app(CBCMapper::class);
    }

    public function generate(array $input): array
    {
        $schoolId = app(SchoolContext::class)->requireId();

        $gradeId   = $input['grade_id'] ?? throw new RuntimeException('grade_id is required');
        $subjectId = $input['subject_id'] ?? throw new RuntimeException('subject_id is required');
        $termLabel = $input['term_label'] ?? 'Term 1';

        $grade   = $this->cbc->getGrade($gradeId);
        $subject = $this->cbc->getSubject($subjectId);

        if (!$grade || !$subject) {
            throw new RuntimeException('Invalid grade or subject for scheme of work.');
        }

        $subjectName = $subject['name'] ?? 'Subject';
        $gradeLabel  = $grade['label'] ?? 'Unknown';

        $weeks = [];
        for ($w = 1; $w <= 13; $w++) {
            $isRevision   = ($w % 4 === 0);
            $isAssessment = ($w % 5 === 0);
            $topic        = $this->resolveWeekTopic($w, $subjectName, $isRevision, $isAssessment);
            $competency   = $this->resolveWeekCompetency($w);

            $weeks[] = [
                'week'   => $w,
                'topic'  => $topic,
                'type'   => match (true) {
                    $isRevision    => 'revision',
                    $isAssessment  => 'assessment',
                    default        => 'new_content',
                },
                'competency' => $competency,
                'learning_outcomes' => [
                    'Define ' . $topic,
                    'Apply ' . $topic . ' to ' . $subjectName . ' problems',
                    'Analyse ' . $topic . ' using CBC criteria',
                ],
                'assessment' => match (true) {
                    $isRevision => [
                        'type'   => 'formative',
                        'method' => $topic . ' ' . $subjectName . ' quiz',
                        'weight' => '10%',
                    ],
                    $isAssessment => [
                        'type'   => 'summative',
                        'method' => $topic . ' ' . $subjectName . ' end-of-topic test',
                        'weight' => '20%',
                    ],
                    default => [
                        'type'   => 'formative',
                        'method' => $topic . ' ' . $subjectName . ' classwork',
                        'weight' => '5%',
                    ],
                },
            ];
        }

        return [
            'term'  => $termLabel . ' — ' . $subjectName . ' (' . $gradeLabel . ')',
            'weeks' => $weeks,
        ];
    }

    private function resolveWeekTopic(int $week, string $subjectName, bool $isRevision, bool $isAssessment): string
    {
        $topics = [
            'Introduction to ' . $subjectName,
            $subjectName . ' Foundations',
            $subjectName . ' Core Concepts',
            $subjectName . ' Application',
            $subjectName . ' Analysis',
            $subjectName . ' Synthesis',
            $subjectName . ' Evaluation',
            $subjectName . ' Advanced Topics',
            $subjectName . ' Integration',
            $subjectName . ' Mastery',
            $subjectName . ' Review',
            $subjectName . ' Exam Preparation',
            $subjectName . ' Final Assessment',
        ];

        $index = ($week - 1) % count($topics);

        if ($isRevision) {
            return $topics[$index] . ' (Revision)';
        }
        if ($isAssessment) {
            return $topics[$index] . ' (Assessment)';
        }
        return $topics[$index];
    }

    private function resolveWeekCompetency(int $week): string
    {
        $competencies = ['Remember', 'Understand', 'Apply', 'Analyze', 'Evaluate', 'Create'];
        return $competencies[($week - 1) % count($competencies)];
    }
}