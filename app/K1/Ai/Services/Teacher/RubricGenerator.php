<?php

namespace App\K1\Ai\Services\Teacher;

use App\K1\Ai\Core\Curriculum\CBCMapper;
use App\Modules\ShuleYetu\Support\Tenancy\SchoolContext;
use RuntimeException;

/**
 * RubricGenerator
 *
 * Generates CBC-aligned assessment rubrics with 4 measurable levels.
 * Each rubric has 3–6 criteria with Excellent / Good / Fair / Needs Improvement levels.
 *
 * @package App\K1\Ai\Services\Teacher
 */
class RubricGenerator
{
    private const LEVELS = ['Excellent', 'Good', 'Fair', 'Needs Improvement'];
    private const SCORE_RANGES = [
        'Excellent' => [80, 100],
        'Good'      => [60, 79],
        'Fair'      => [40, 59],
        'Needs Improvement' => [0, 39],
    ];

    private CBCMapper $cbc;

    public function __construct()
    {
        $this->cbc = app(CBCMapper::class);
    }

    /**
     * Generate a complete assessment rubric.
     *
     * @param array $input {
     * @type string $subject     Subject name
     * @type string $task_type   e.g. 'assignment', 'exam', 'project'
     * @type int    $criteria_count  Number of criteria (3-6)
     * }
     *
     * @return array{title: string, criteria: array[], levels: string[], scores: array}
     */
    public function generate(array $input): array
    {
        $schoolId = app(SchoolContext::class)->requireId();

        $subject = $input['subject'] ?? throw new RuntimeException('subject is required');
        $taskType = $input['task_type'] ?? 'assignment';
        $criteriaCount = min(max($input['criteria_count'] ?? 4, 3), 6);

        // Build criteria
        $criteria = [];
        $standardCriteria = [
            'Content Accuracy',
            'Application of Concepts',
            'Critical Thinking',
            'Presentation & Clarity',
            'Timeliness & Completion',
            'Collaboration & Teamwork',
        ];

        for ($i = 0; $i < $criteriaCount; $i++) {
            $label = $standardCriteria[$i] ?? "Criterion " . ($i + 1);

            $levels = [];
            foreach (self::LEVELS as $level) {
                $scoreRange = self::SCORE_RANGES[$level];
                $levels[$level] = "{$this->levelDescription($label, $level)} ({$scoreRange[0]}-{$scoreRange[1]}%)";
            }

            $criteria[] = [
                'name'  => $label,
                'weight' => round(100 / $criteriaCount, 1),
                'description' => $levels,
            ];
        }

        return [
            'title'     => "{$subject} — {$taskType} Assessment Rubric",
            'criteria'  => $criteria,
            'levels'    => self::LEVELS,
            'scores'    => [
                'total'       => 100,
                'pass_mark'   => 40,
                'distinction' => 80,
            ],
        ];
    }

    private function levelDescription(string $criterion, string $level): string
    {
        return match ($level) {
            'Excellent'           => "Completely demonstrates {$criterion} with no errors or omissions",
            'Good'                => "{$criterion} demonstrated with minor gaps — mostly accurate",
            'Fair'               => "Partial {$criterion} — some key elements missing but effort visible",
            'Needs Improvement'  => "{$criterion} not yet demonstrated — requires significant rework",
        };
    }
}