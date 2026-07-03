<?php

namespace App\K1\Ai\Engine;

use App\K1\Ai\Core\Curriculum\CBCMapper;
use App\K1\Ai\Core\Validators\CurriculumOutputValidator;

/**
 * CurriculumGuard — Prevents AI from generating invalid or non-CBC-compliant output.
 *
 * Acts as a gatekeeper: all AI output must pass through this guard
 * before being returned to the API response.
 *
 * @package App\K1\Ai\Engine
 */
class CurriculumGuard
{
    private CBCMapper $cbc;
    private CurriculumOutputValidator $validator;

    public function __construct()
    {
        $this->cbc      = app(CBCMapper::class);
        $this->validator = new CurriculumOutputValidator();
    }

    /**
     * Validate and sanitize any AI-generated output.
     *
     * @return array{passed: bool, output: array|null, violations: string[]}
     */
    public function guard(array $output, string $type = 'lesson_plan'): array
    {
        $this->cbc->load();

        $requiredFields = match ($type) {
            'lesson_plan'   => ['lesson_title', 'grade', 'subject', 'competency', 'learning_outcomes'],
            'scheme_of_work' => ['term', 'weeks'],
            'rubric'        => ['title', 'criteria', 'levels'],
            'activities'    => ['group_work', 'individual_exercises', 'homework'],
        };

        $violations = [];
        foreach ($requiredFields as $field) {
            if (!isset($output[$field]) || (is_array($output[$field]) && count($output[$field]) === 0)) {
                $violations[] = "Missing required field: $field";
            }
        }

        $validatorResult = $this->validator->validateLessonPlan($output);

        return [
            'passed' => empty($violations) && $validatorResult['valid'],
            'output' => empty($violations) ? $output : null,
            'violations' => array_merge($violations, $validatorResult['errors']),
        ];
    }
}