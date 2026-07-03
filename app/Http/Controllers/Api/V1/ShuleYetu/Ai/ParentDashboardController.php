<?php

namespace App\Http\Controllers\Api\V1\ShuleYetu\Ai;

use App\Http\Controllers\Controller;
use App\K1\Ai\Parent\StudentSimplifiedReportGenerator;
use App\K1\Ai\Parent\HomeInterventionAdvisor;
use App\K1\Ai\Parent\ProgressExplainer;
use App\K1\Ai\Parent\ParentNotificationIntelligence;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ParentDashboardController extends Controller
{
    private StudentSimplifiedReportGenerator $reports;
    private HomeInterventionAdvisor $interventions;
    private ProgressExplainer $explainers;
    private ParentNotificationIntelligence $notifications;

    public function __construct()
    {
        $this->reports       = app(StudentSimplifiedReportGenerator::class);
        $this->interventions  = app(HomeInterventionAdvisor::class);
        $this->explainers    = app(ProgressExplainer::class);
        $this->notifications  = app(ParentNotificationIntelligence::class);
    }

    public function studentReport(string $student): JsonResponse
    {
        $this->authorizePermission('ai.parent.view');
        return response()->json($this->reports->generate($student));
    }

    public function homeIntervention(Request $request): JsonResponse
    {
        $this->authorizePermission('ai.parent.view');
        return response()->json($this->interventions->suggest($request->input('student_id')));
    }

    public function progressSummary(string $student): JsonResponse
    {
        $this->authorizePermission('ai.parent.view');
        return response()->json($this->explainers->explain($student));
    }

    private function authorizePermission(string $permission): void
    {
        abort_unless(auth()->user()?->hasPermission($permission), 403);
    }
}