<?php

namespace App\K1\Ai\Services\Teacher;

use App\K1\Ai\Core\Curriculum\CBCMapper;
use App\K1\Ai\Core\Validators\CurriculumOutputValidator;
use App\Modules\ShuleYetu\Support\Tenancy\SchoolContext;
use RuntimeException;

/**
 * LessonPlanGenerator
 *
 * Generates structured, CBC-aligned lesson plans.
 * Every output is a JSON-serializable array — no free text.
 *
 * @package App\K1\Ai\Services\Teacher
 */
class LessonPlanGenerator
{
    private CBCMapper $cbc;
    private CurriculumOutputValidator $validator;

    public function __construct()
    {
        $this->cbc = app(CBCMapper::class);
        $this->validator = new CurriculumOutputValidator();
    }

    /**
     * Generate a complete lesson plan from a structured request.
     *
     * @param array $input {
     * @type string $grade_id       CBC grade identifier
     * @type string $subject_id     Subject identifier
     * @type string $topic          Lesson topic name
     * @type string $competency    Target competency (e.g. 'Apply', 'Analyze')
     * @type string $learning_phase 'introduction' | 'development' | 'conclusion'
     * }
     *
     * @return array{
     *     lesson_title: string,
     *     grade: string,
     *     subject: string,
     *     competency: string,
     *     learning_outcomes: string[],
     *     structure: array{phase: string, duration: int, activity: string}[],
     *     assessment_criteria: string[],
     *     differentiation: array{for_slow_learners: string[], for_fast_learners: string[]},
     *     teaching_aids: string[]
     * }
     */
    public function generate(array $input): array
    {
        $schoolId = app(SchoolContext::class)->requireId();

        // 1. Load curriculum context
        $curriculum = $this->cbc->load();
        $gradeId    = $input['grade_id'] ?? throw new RuntimeException('grade_id is required');
        $subjectId  = $input['subject_id'] ?? throw new RuntimeException('subject_id is required');
        $topic      = $input['topic'] ?? 'General';
        $competency = $input['competency'] ?? 'Apply';

        // 2. Map grade + subject to curriculum
        $grade   = $this->cbc->getGrade($gradeId);
        $subject = $this->cbc->getSubject($subjectId);

        if (!$grade || !$subject) {
            throw new RuntimeException('Invalid grade or subject for CBC curriculum.');
        }

        // 3. Resolve competency band
        $band = $this->cbc->resolveCompetency($competency === 'Apply' ? 70.0 : 60.0);

        // 4. Generate structured lesson (no free text)
        $phase = $input['learning_phase'] ?? 'introduction';
        $learningOutcomes = $this->buildLearningOutcomes($topic, $competency, $band);

        // Duration per phase (CBC standard: 35 min lesson)
        $durations = [
            'introduction' => 5,
            'development'  => 20,
            'conclusion'  => 10,
        ];

        $structure = [
            ['phase' => 'Introduction', 'duration' => $durations['introduction'], 'activity' => "Hook: {$this->hookActivity($topic)}"],
            ['phase' => 'Development',  'duration' => $durations['development'],  'activity' => "{$this->teachingActivity($topic, $competency)}"],
            ['phase' => 'Conclusion',   'duration' => $durations['conclusion'],   'activity' => "{$this->assessmentActivity($topic)}"],
        ];

        return [
            'lesson_title'       => "{$subject['name']}: {$topic}",
            'grade'              => $grade['label'] ?? 'Unknown',
            'subject'            => $subject['name'] ?? 'Unknown',
            'competency'         => $band,
            'learning_outcomes'  => $learningOutcomes,
            'structure'          => $structure,
            'assessment_criteria' => $this->buildAssessment($topic, $band),
            'differentiation'   => [
                'for_slow_learners' => ["Guided practice with {$subject['name']} worksheets", "Peer-support grouping"],
                'for_fast_learners' => ["Extension {$subject['name']} exercises", "Cross-topic project work"],
            ],
            'teaching_aids'     => ["{$subject['name']} textbook", "CBC {$grade['label']} worksheets", "Whiteboard", "Demonstration materials"],
        ];
    }

    /**
     * Build measurable learning outcomes.
     */
    private function buildLearningOutcomes(string $topic, string $competency, string $band): array
    {
        $base = "By the end of the lesson, learners will be able to:";

        return [
            "Identify key concepts in {$topic}",
            "Apply {$topic} principles to {$competency} scenarios",
            "Analyse {$topic} using {$band} framework",
            "Evaluate {$topic} outcomes with peer feedback",
        ];
    }

    /**
     * Generate an engaging hook activity.
     */
    private function hookActivity(string $topic): string
    {
        return "{$topic} — {$topic} brainstorming using real-world examples";
    }

    /**
     * Core teaching activity.
     */
    private function teachingActivity(string $topic, string $competency): string
    {
        return "{$competency} — {$competency} principles via {$topic} in small groups";
    }

    /**
     * Quick assessment check.
     */
    private function assessmentActivity(string $topic): string
    {
        return "{$topic} — {$topic} reflection: 'What did I learn today?'";
    }

    /**
     * Build assessment criteria.
     */
    private function buildAssessment(string $topic, string $band): array
    {
        return [
            "Demonstrates {$topic} understanding at {$band} level",
            "Applies {$topic} to {$band} scenario correctly",
            "Explains {$topic} reasoning to peers",
            "Identifies {$topic} errors in {$band} examples",
        ];
    }
}