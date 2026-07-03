<?php

namespace App\K1\Ai\Engine;

/**
 * OutputNormalizer — Ensures all AI output follows a consistent, predictable structure.
 *
 * All API responses must be:
 * - structured JSON (no free text)
 * - consistently formatted keys
 * - never contain vague or non-measurable content
 *
 * @package App\K1\Ai\Engine
 */
class OutputNormalizer
{
    /**
     * Normalize a lesson plan to the standard output schema.
     */
    public function normalizeLessonPlan(array $input): array
    {
        return [
            'lesson_title'      => $input['lesson_title'] ?? '',
            'grade'             => $input['grade'] ?? '',
            'subject'           => $input['subject'] ?? '',
            'competency'        => $input['competency'] ?? '',
            'learning_outcomes' => $input['learning_outcomes'] ?? [],
            'structure'         => $input['structure'] ?? [],
            'assessment_criteria' => $input['assessment_criteria'] ?? [],
            'differentiation'   => $input['differentiation'] ?? [],
            'teaching_aids'     => $input['teaching_aids'] ?? [],
        ];
    }

    /**
     * Normalize a scheme of work.
     */
    public function normalizeSchemeOfWork(array $input): array
    {
        return [
            'term'  => $input['term'] ?? '',
            'weeks' => $input['weeks'] ?? [],
        ];
    }

    /**
     * Normalize a rubric.
     */
    public function normalizeRubric(array $input): array
    {
        return [
            'title'    => $input['title'] ?? '',
            'criteria' => $input['criteria'] ?? [],
            'levels'   => $input['levels'] ?? [],
            'scores'   => $input['scores'] ?? [],
        ];
    }

    /**
     * Normalize activities.
     */
    public function normalizeActivities(array $input): array
    {
        return [
            'group_work'          => $input['group_work'] ?? [],
            'individual_exercises' => $input['individual_exercises'] ?? [],
            'homework'            => $input['homework'] ?? [],
            'engagement_games'    => $input['engagement_games'] ?? [],
        ];
    }
}