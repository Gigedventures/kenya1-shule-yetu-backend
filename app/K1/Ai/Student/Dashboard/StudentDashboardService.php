<?php

namespace App\K1\Ai\Student\Dashboard;

use App\K1\Ai\Services\StudentPerformancePredictor;
use App\K1\Ai\Services\CompetencyGapAnalyzer;
use App\K1\Ai\Services\AtRiskDetector;
use App\K1\Ai\Services\LearningPlanGenerator;
use App\Modules\ShuleYetu\Models\ShuleStudent;
use App\Modules\ShuleYetu\Models\ShuleExamScore;
use App\Modules\ShuleYetu\Models\ShuleTermResult;
use App\Modules\ShuleYetu\Support\Tenancy\SchoolContext;
use Illuminate\Support\Facades\DB;

class StudentDashboardService
{
    public function __construct(
        private StudentPerformancePredictor $predictor,
        private CompetencyGapAnalyzer $gaps,
        private AtRiskDetector $risk,
        private LearningPlanGenerator $plans,
    ) {}

    /**
     * Build a complete student dashboard for any level.
     *
     * @param string $studentId
     * @param string $level 'primary' | 'junior' | 'senior'
     * @return array{dashboard: array, ai: array, level: string}
     */
    public function build(string $studentId, string $level = 'senior'): array
    {
        $schoolId = app(SchoolContext::class)->requireId();
        $student = ShuleStudent::findOrFail($studentId);

        // Core data
        $predictions = $this->predictor->predict($studentId);
        $gaps = $this->gaps->analyze($studentId);
        $risk = $this->risk->detect($studentId);
        $plan = $this->plans->generate($predictions['weakest_subjects'], $predictions['strongest_subjects'], $gaps['competency_gaps']);

        // Level-based adaptation
        $widgets = match ($level) {
            'primary' => ['large_cards' => true, 'navigation' => 'simple', 'features' => ['attendance', 'homework', 'rewards']],
            'junior'  => ['large_cards' => false, 'navigation' => 'subject', 'features' => ['competency', 'revision', 'skills']],
            'senior'  => ['large_cards' => false, 'navigation' => 'advanced', 'features' => ['analytics', 'career', 'university']],
            default   => ['large_cards' => true, 'navigation' => 'simple', 'features' => ['attendance', 'homework']],
        };

        return [
            'student' => [
                'id'   => $student->id,
                'name' => trim($student->first_name . ' ' . $student->last_name),
                'level' => $level,
                'class' => $student->current_class_id,
            ],
            'academic' => [
                'predicted_average' => $predictions['predicted_average'],
                'risk_level'       => $predictions['risk_level'],
                'strongest'        => $predictions['strongest_subjects'],
                'weakest'          => $predictions['weakest_subjects'],
            ],
            'competency' => [
                'gaps'       => $gaps['competency_gaps'],
                'strengths'  => $gaps['strengths'],
                'interventions' => $gaps['interventions'],
            ],
            'risk' => [
                'score'        => $risk['risk_score'],
                'level'        => $risk['risk_level'],
                'reasons'      => $risk['reasons'],
                'actions'      => $risk['recommended_actions'],
            ],
            'learning_plan' => [
                'total_sessions' => $plan['total_sessions'],
                'estimated_hours' => $plan['estimated_hours'],
                'days'           => $plan['plan'],
            ],
            'widgets' => $widgets,
        ];
    }
}