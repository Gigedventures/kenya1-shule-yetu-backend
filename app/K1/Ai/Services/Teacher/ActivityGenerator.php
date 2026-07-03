<?php

namespace App\K1\Ai\Services\Teacher;

use App\K1\Ai\Core\Curriculum\CBCMapper;
use App\Modules\ShuleYetu\Support\Tenancy\SchoolContext;
use RuntimeException;

class ActivityGenerator
{
    private CBCMapper $cbc;

    public function __construct()
    {
        $this->cbc = app(CBCMapper::class);
    }

    public function generate(array $input): array
    {
        $schoolId = app(SchoolContext::class)->requireId();

        $gradeId    = $input['grade_id'] ?? throw new RuntimeException('grade_id is required');
        $subjectId  = $input['subject_id'] ?? throw new RuntimeException('subject_id is required');
        $topic      = $input['lesson_topic'] ?? 'General';
        $studentCount = $input['student_count'] ?? 30;

        $grade   = $this->cbc->getGrade($gradeId);
        $subject = $this->cbc->getSubject($subjectId);

        if (!$grade || !$subject) {
            throw new RuntimeException('Invalid grade or subject for classroom activity generation.');
        }

        $groupSize = min(max(4, (int) ($studentCount / 6)), 6);

        $subjectName = $subject['name'] ?? 'Subject';

        return [
            'group_work' => [
                [
                    'title'       => $subjectName . ' Topic Brainstorm',
                    'groups'      => ceil($studentCount / $groupSize),
                    'per_group'  => $groupSize,
                    'duration_min' => 15,
                    'instructions' => 'In groups, list ' . $topic . ' applications in real life',
                ],
                [
                    'title'       => $subjectName . ' ' . $topic . ' Problem Set',
                    'groups'      => ceil($studentCount / ($groupSize * 0.5)),
                    'per_group'  => intval($groupSize * 0.5),
                    'duration_min' => 20,
                    'instructions' => 'Solve ' . $topic . ' problems collaboratively',
                ],
            ],
            'individual_exercises' => [
                [
                    'title'       => $topic . ' Comprehension Check',
                    'duration_min' => 10,
                    'format'      => 'Worksheet',
                    'questions'   => 5,
                ],
                [
                    'title'       => $topic . ' ' . $subjectName . ' Reflection',
                    'duration_min' => 5,
                    'format'      => 'Journal',
                    'questions'   => 1,
                ],
            ],
            'homework' => [
                [
                    'title'       => $topic . ' ' . $subjectName . ' Application',
                    'estimated_min' => 20,
                    'type'       => $topic . ' ' . $subjectName . ' real-world scenario',
                    'due'        => 'Next lesson',
                ],
                [
                    'title'       => $topic . ' ' . $subjectName . ' Extension',
                    'estimated_min' => 15,
                    'type'       => $topic . ' ' . $subjectName . ' practice',
                    'due'        => 'Next lesson',
                ],
            ],
            'engagement_games' => [
                [
                    'title'  => $topic . ' Quiz Challenge',
                    'type'   => $topic . ' ' . $subjectName,
                    'players' => $studentCount,
                    'rounds' => 3,
                ],
                [
                    'title'  => $topic . ' ' . $subjectName . ' Relay',
                    'type'   => $topic . ' ' . $subjectName,
                    'players' => $studentCount,
                    'rounds' => 2,
                ],
            ],
        ];
    }
}