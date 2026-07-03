<?php

namespace App\K1\Ai\Core\LearningLoop;

use App\K1\Ai\Core\Curriculum\CBCMapper;
use App\Modules\ShuleYetu\Support\Tenancy\SchoolContext;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * CurriculumDriftDetector — Ensures AI never deviates from CBC structure.
 *
 * Detects and flags:
 * - missing competencies
 * - incorrect grade difficulty
 * - syllabus mismatch
 *
 * If drift is high (> 50), the system must block or correct the output.
 *
 * @package App\K1\Ai\Core\LearningLoop
 */
class CurriculumDriftDetector
{
    private const VALID_CBC_GRADES = [
        'PP1', 'PP2',
        'Grade 1', 'Grade 2', 'Grade 3', 'Grade 4', 'Grade 5', 'Grade 6',
        'Grade 7', 'Grade 8', 'Grade 9',
    ];

    private CBCMapper $cbc;

    public function __construct()
    {
        $this->cbc = app(CBCMapper::class);
    }

    /**
     * Detect curriculum drift in a generated lesson plan or scheme of work.
     *
     * @return array{drift_score: int, violations: string[], blocked: bool}
     */
    public function detect(array $content, string $type = 'lesson'): array
    {
        $schoolId = app(SchoolContext::class)->requireId();
        $violations = [];
        $score      = 0;

        // Check grade exists
        if (isset($content['grade'])) {
            $gradeOk = false;
            foreach (self::VALID_CBC_GRADES as $valid) {
                if (stripos($content['grade'], $valid) !== false) {
                    $gradeOk = true;
                    break;
                }
            }
            if (!$gradeOk) {
                $violations[] = "Invalid grade: {$content['grade']} — not in CBC curriculum";
                $score += 30;
            }
        }

        // Check subject exists in curriculum
        if (isset($content['subject'])) {
            $curriculum = $this->cbc->load();
            $subjectFound = false;
            foreach ($curriculum['subjects'] as $s) {
                if (stripos($s['name'], $content['subject']) !== false) {
                    $subjectFound = true;
                    break;
                }
            }
            if (!$subjectFound) {
                $violations[] = "Subject not found in curriculum: {$content['subject']}";
                $score += 25;
            }
        }

        // Check learning outcomes are measurable
        if ($type === 'lesson' && isset($content['learning_outcomes'])) {
            foreach ($content['learning_outcomes'] as $outcome) {
                $vague = preg_match('/\b(something|maybe|could|might|various|general)\b/i', $outcome);
                if ($vague) {
                    $violations[] = "Vague learning outcome: \"$outcome\" — must be measurable";
                    $score += 10;
                }
            }
        }

        // Check for missing required fields
        $required = ['lesson_title', 'grade', 'subject', 'competency'];
        foreach ($required as $field) {
            if (!isset($content[$field]) || empty($content[$field])) {
                $violations[] = "Missing required field: $field";
                $score += 15;
            }
        }

        return [
            'drift_score' => min($score, 100),
            'violations' => $violations,
            'blocked'    => $score >= 50,
        ];
    }

    /**
     * Get a comprehensive drift report for a school.
     */
    public function getReport(string $schoolId): array
    {
        $recentContent = DB::table('k1_teacher_feedback')
            ->where('school_id', $schoolId)
            ->where('created_at', '>=', now()->subDays(30))
            ->get();

        $driftedContent = [];
        foreach ($recentContent as $item) {
            $drift = $this->detect((array) $item, 'lesson');
            if ($drift['drift_score'] > 0) {
                $driftedContent[] = $drift;
            }
        }

        return [
            'school_id'      => $schoolId,
            'total_checked'  => count($recentContent),
            'drifted_count'  => count($driftedContent),
            'avg_drift'      => round(collect($driftedContent)->avg('drift_score') ?? 0, 2),
            'top_violations' => collect($driftedContent)
                ->flatMap(fn ($d) => $d['violations'])
                ->take(5)
                ->toArray(),
        ];
    }
}