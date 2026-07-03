<?php

namespace App\Http\Controllers\Api\V1\ShuleYetu\Ai;

use App\Http\Controllers\Controller;
use App\K1\Ai\Services\StudentPerformancePredictor;
use App\K1\Ai\Services\AtRiskDetector;
use App\K1\Ai\Services\CompetencyGapAnalyzer;
use App\K1\Ai\Services\LearningPlanGenerator;
use Illuminate\Http\JsonResponse;

class AiController extends Controller
{
    public function predict(string $student, StudentPerformancePredictor $service): JsonResponse
    {
        $this->authorizePermission('ai.scores.view');

        $result = $service->predict($student);

        return response()->json($result);
    }

    public function risk(string $student, AtRiskDetector $service): JsonResponse
    {
        $this->authorizePermission('ai.scores.view');

        $result = $service->detect($student);

        return response()->json($result);
    }

    public function competencyGaps(string $student, CompetencyGapAnalyzer $service): JsonResponse
    {
        $this->authorizePermission('ai.scores.view');

        $result = $service->analyze($student);

        return response()->json($result);
    }

    public function learningPlan(string $student, LearningPlanGenerator $service): JsonResponse
    {
        $this->authorizePermission('ai.scores.view');

        $result = $service->generate([], []);

        return response()->json($result);
    }

    private function authorizePermission(string $permission): void
    {
        abort_unless(auth()->user()?->hasPermission($permission), 403);
    }
}