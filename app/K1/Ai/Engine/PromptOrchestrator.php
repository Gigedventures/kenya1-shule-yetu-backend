<?php

namespace App\K1\Ai\Engine;

use App\K1\Ai\Core\Curriculum\CBCMapper;
use App\K1\Ai\Core\Analytics\SchoolAIProfileBuilder;
use App\K1\Ai\Core\LearningLoop\TeacherFeedbackCollector;
use App\K1\Ai\Core\LearningLoop\AdaptiveWeightsEngine;
use App\K1\Ai\Core\LearningLoop\CurriculumDriftDetector;
use App\K1\Ai\Services\Teacher\LessonPlanGenerator;
use App\K1\Ai\Services\Teacher\SchemeOfWorkGenerator;
use App\K1\Ai\Services\Teacher\RubricGenerator;
use App\K1\Ai\Services\Teacher\ActivityGenerator;
use RuntimeException;

/**
 * PromptOrchestrator — CENTRAL CONTROL TOWER.
 *
 * The single entry point for ALL AI generation in K1.
 *
 * Flow:
 *   CBCMapper → SchoolAIProfile → TeacherFeedback → AdaptiveWeights → Generator
 *
 * @package App\K1\Ai\Engine
 */
class PromptOrchestrator
{
    private CBCMapper $cbc;
    private SchoolAIProfileBuilder $profile;
    private TeacherFeedbackCollector $feedback;
    private AdaptiveWeightsEngine $weights;
    private CurriculumDriftDetector $drift;

    private LessonPlanGenerator $lessonPlans;
    private SchemeOfWorkGenerator $schemes;
    private RubricGenerator $rubrics;
    private ActivityGenerator $activities;

    public function __construct()
    {
        $this->cbc      = app(CBCMapper::class);
        $this->profile  = app(SchoolAIProfileBuilder::class);
        $this->feedback = app(TeacherFeedbackCollector::class);
        $this->weights  = app(AdaptiveWeightsEngine::class);
        $this->drift    = app(CurriculumDriftDetector::class);
        $this->lessonPlans = app(LessonPlanGenerator::class);
        $this->schemes     = app(SchemeOfWorkGenerator::class);
        $this->rubrics     = app(RubricGenerator::class);
        $this->activities   = app(ActivityGenerator::class);
    }

    /**
     * Route a generation request through the full pipeline.
     *
     * @param string $type  'lesson_plan' | 'scheme_of_work' | 'rubric' | 'activities'
     * @param array  $input The input data
     *
     * @return array{result: array|null, blocked: bool, reason: string|null}
     */
    public function route(string $type, array $input): array
    {
        // 1. Load curriculum context
        $curriculum = $this->cbc->load();

        // 2. Get school profile if available
        $schoolId = $input['school_id'] ?? null;
        if ($schoolId) {
            $profile = $this->profile->build($schoolId);
        }

        // 3. Check for curriculum drift
        $drift = $this->drift->detect($input, 'lesson');
        if ($drift['blocked']) {
            return [
                'result'  => null,
                'blocked' => true,
                'reason'  => 'Curriculum drift detected: ' . implode('; ', $drift['violations']),
            ];
        }

        // 4. Apply adaptive weights
        if ($schoolId && isset($input['subject'])) {
            $weights = $this->weights->calculateWeights($input['subject'], $input['grade'] ?? 'General', $schoolId);
        }

        // 5. Route to correct service
        $result = match ($type) {
            'lesson_plan'   => $this->lessonPlans->generate($input),
            'scheme_of_work' => $this->schemes->generate($input),
            'rubric'        => $this->rubrics->generate($input),
            'activities'    => $this->activities->generate($input),
            default         => throw new RuntimeException("Unknown generation type: $type"),
        };

        return [
            'result'  => $result,
            'blocked' => false,
            'reason'  => null,
        ];
    }
}