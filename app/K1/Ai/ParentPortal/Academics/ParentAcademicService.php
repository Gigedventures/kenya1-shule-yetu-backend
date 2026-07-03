<?php

namespace App\K1\Ai\ParentPortal\Academics;

use App\K1\Ai\Services\StudentPerformancePredictor;
use App\K1\Ai\Services\CompetencyGapAnalyzer;
use App\K1\Ai\Services\LearningPlanGenerator;
use App\K1\Ai\Parent\HomeInterventionAdvisor;
use App\K1\Ai\Parent\StudentSimplifiedReportGenerator;
use App\Modules\ShuleYetu\Support\Tenancy\SchoolContext;

class ParentAcademicService
{
    public function __construct(
        private StudentPerformancePredictor $predict,
        private CompetencyGapAnalyzer $gaps,
        private LearningPlanGenerator $plans,
        private HomeInterventionAdvisor $interventions,
        private StudentSimplifiedReportGenerator $reports,
    ) {}

    public function getInsights(string $studentId): array
    {
        $predictions = $this->predict->predict($studentId);
        $report = $this->reports->generate($studentId);
        $interventions = $this->interventions->suggest($studentId);

        return [
            'predicted_average' => $predictions['predicted_average'],
            'risk_level' => $predictions['risk_level'],
            'strongest' => $predictions['strongest_subjects'],
            'weakest' => $predictions['weakest_subjects'],
            'progress' => $report['progress'],
            'routines' => $interventions['routine'],
            'activities' => $interventions['activities'],
        ];
    }
}