<?php

namespace App\K1\Ai\Student\AiAssistant;

use App\K1\Ai\Services\StudentPerformancePredictor;
use App\K1\Ai\Services\CompetencyGapAnalyzer;
use App\K1\Ai\Core\Curriculum\CBCMapper;
use App\Modules\ShuleYetu\Support\Tenancy\SchoolContext;

/**
 * StudentAiAssistant — AI companion for students.
 * Explains topics, homework, assignments, mistakes, and suggests improvements.
 * All responses must be grade-appropriate (no technical jargon for primary).
 */
class StudentAiAssistant
{
    public function __construct(
        private StudentPerformancePredictor $predictor,
        private CompetencyGapAnalyzer $gaps,
        private CBCMapper $cbc,
    ) {}

    /**
     * Explain a topic in simple, age-appropriate terms.
     */
    public function explain(string $topic, string $level = 'primary'): array
    {
        $complexity = $level === 'primary' ? 'simple' : ($level === 'junior' ? 'clear' : 'detailed');

        return match ($complexity) {
            'simple'  => ['explanation' => "{$topic} is a school subject. It helps you learn and practice."],
            'clear'   => ['explanation' => "{$topic} — focus on the key concepts and try practice questions."],
            'detailed' => ['explanation' => "{$topic} — {$this->cbc->resolveCompetency(70)}. Review exercises available."],
        };
    }

    /**
     * Generate a revision plan.
     */
    public function generateRevision(string $studentId): array
    {
        $predictions = $this->predictor->predict($studentId);
        return [
            'subject' => $predictions['weakest_subjects'][0] ?? 'General',
            'plan' => 'Study 20 min daily on weak areas',
            'materials' => 'Topic notes + practice questions',
        ];
    }

    /**
     * Answer a curriculum question.
     */
    public function answerCbcQuestion(string $question): array
    {
        $curriculum = $this->cbc->load();
        $subjects = collect($curriculum['subjects']);
        $matched = $subjects->filter(fn($s) => stripos($s['name'] ?? '', $question) !== false);
        return ['question' => $question, 'matches' => $matched->values()->toArray()];
    }
}