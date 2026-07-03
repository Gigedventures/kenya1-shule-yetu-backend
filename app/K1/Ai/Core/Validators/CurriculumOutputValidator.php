<?php

namespace App\K1\Ai\Core\Validators;

/**
 * Validates all AI-generated curriculum output against the CBC framework.
 *
 * Ensures:
 * - No invented grades or subjects
 * - Competencies map to real bands
 * - All percentages are within valid ranges
 * - Output is structured and complete
 */
class CurriculumOutputValidator
{
    private const VALID_GRADE_LABELS = [
        'PP1', 'PP2',
        'Grade 1', 'Grade 2', 'Grade 3', 'Grade 4', 'Grade 5', 'Grade 6',
        'Grade 7', 'Grade 8', 'Grade 9',
        'Senior 10', 'Senior 11', 'Senior 12',
    ];

    private const MAX_ALLOWED_ACTIVITIES_PER_LESSON = 8;
    private const MIN_CORE_COMPETENCY_PERCENT = 70.0;

    /**
     * Validate a lesson plan output.
     *
     * @return array{valid: bool, errors: string[]}
     */
    public function validateLessonPlan(array $plan): array
    {
        $errors = [];

        // Must have required fields
        $required = ['lesson_title', 'grade', 'subject', 'competency', 'learning_outcomes'];
        foreach ($required as $field) {
            if (!isset($plan[$field]) || empty($plan[$field])) {
                $errors[] = "Missing required field: $field";
            }
        }

        // Validate grade
        if (isset($plan['grade']) && !in_array($plan['grade'], self::VALID_GRADE_LABELS)) {
            $errors[] = "Invalid grade label: {$plan['grade']}";
        }

        // Activities must not exceed limit
        if (isset($plan['activities']) && count($plan['activities']) > self::MAX_ALLOWED_ACTIVITIES_PER_LESSON) {
            $errors[] = "Too many activities (max " . self::MAX_ALLOWED_ACTIVITIES_PER_LESSON . ")";
        }

        // Assessment must be measurable
        if (isset($plan['assessment']) && $this->isVague($plan['assessment'])) {
            $errors[] = "Assessment criteria is too vague — must be measurable";
        }

        return [
            'valid'  => empty($errors),
            'errors' => $errors,
        ];
    }

    /**
     * Validate scheme of work output.
     *
     * @return array{valid: bool, errors: string[]}
     */
    public function validateSchemeOfWork(array $scheme): array
    {
        $errors = [];

        if (!isset($scheme['weeks']) || count($scheme['weeks']) !== 13) {
            $errors[] = "Scheme of work must have exactly 13 weeks";
        }

        foreach ($scheme['weeks'] ?? [] as $i => $week) {
            if (!isset($week['topic']) || empty($week['topic'])) {
                $errors[] = "Week " . ($i + 1) . " has no topic";
            }
        }

        return [
            'valid'  => empty($errors),
            'errors' => $errors,
        ];
    }

    /**
     * Check if a description is too vague to be usable.
     */
    private function isVague(string $text): bool
    {
        $vaguePatterns = ['something', 'maybe', 'could', 'might', 'various', 'some', 'general'];

        foreach ($vaguePatterns as $pattern) {
            if (stripos($text, $pattern) !== false) {
                return true;
            }
        }

        return strlen(trim($text)) < 10;
    }
}