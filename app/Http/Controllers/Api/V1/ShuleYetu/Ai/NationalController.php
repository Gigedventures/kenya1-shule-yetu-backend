<?php

namespace App\Http\Controllers\Api\V1\ShuleYetu\Ai;

use App\Http\Controllers\Controller;
use App\K1\Ai\National\SchoolBenchmarkEngine;
use App\K1\Ai\National\CountyPerformanceAggregator;
use App\K1\Ai\National\EducationTrendAnalyzer;
use App\K1\Ai\National\NationalInsightsGenerator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NationalController extends Controller
{
    private SchoolBenchmarkEngine $benchmark;
    private CountyPerformanceAggregator $county;
    private EducationTrendAnalyzer $trends;
    private NationalInsightsGenerator $insights;

    public function __construct()
    {
        $this->benchmark = app(SchoolBenchmarkEngine::class);
        $this->county    = app(CountyPerformanceAggregator::class);
        $this->trends    = app(EducationTrendAnalyzer::class);
        $this->insights  = app(NationalInsightsGenerator::class);
    }

    public function schoolBenchmark(): JsonResponse
    {
        $this->authorizePermission('ai.national.view');
        return response()->json($this->benchmark->benchmark());
    }

    public function countyPerformance(): JsonResponse
    {
        $this->authorizePermission('ai.national.view');
        return response()->json($this->county->aggregate());
    }

    public function nationalTrends(): JsonResponse
    {
        $this->authorizePermission('ai.national.view');
        return response()->json($this->trends->analyze());
    }

    public function nationalReport(): JsonResponse
    {
        $this->authorizePermission('ai.national.view');
        return response()->json($this->insights->generate());
    }

    private function authorizePermission(string $permission): void
    {
        abort_unless(auth()->user()?->hasPermission($permission), 403);
    }
}