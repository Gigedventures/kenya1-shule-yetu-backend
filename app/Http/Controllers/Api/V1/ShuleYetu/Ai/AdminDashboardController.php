<?php

namespace App\Http\Controllers\Api\V1\ShuleYetu\Ai;

use App\Http\Controllers\Controller;
use App\K1\Ai\Admin\SchoolPerformanceDashboard;
use App\K1\Ai\Admin\StaffPerformanceAnalyzer;
use App\K1\Ai\Admin\CurriculumEfficiencyAnalyzer;
use App\K1\Ai\Admin\ResourceUtilizationTracker;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminDashboardController extends Controller
{
    private SchoolPerformanceDashboard $dashboard;
    private StaffPerformanceAnalyzer $staff;
    private CurriculumEfficiencyAnalyzer $curriculum;
    private ResourceUtilizationTracker $resources;

    public function __construct()
    {
        $this->dashboard  = app(SchoolPerformanceDashboard::class);
        $this->staff      = app(StaffPerformanceAnalyzer::class);
        $this->curriculum = app(CurriculumEfficiencyAnalyzer::class);
        $this->resources  = app(ResourceUtilizationTracker::class);
    }

    public function schoolDashboard(string $school): JsonResponse
    {
        $this->authorizePermission('ai.admin.view');
        return response()->json($this->dashboard->build($school));
    }

    public function staffPerformance(string $school): JsonResponse
    {
        $this->authorizePermission('ai.admin.view');
        return response()->json($this->staff->analyze($school));
    }

    public function curriculumEfficiency(string $school): JsonResponse
    {
        $this->authorizePermission('ai.admin.view');
        return response()->json($this->curriculum->analyze($school));
    }

    private function authorizePermission(string $permission): void
    {
        abort_unless(auth()->user()?->hasPermission($permission), 403);
    }
}