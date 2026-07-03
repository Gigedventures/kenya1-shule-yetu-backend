<?php

namespace App\K1\Ai\National;

use App\K1\Ai\National\SchoolBenchmarkEngine;
use App\K1\Ai\National\CountyPerformanceAggregator;
use App\K1\Ai\National\EducationTrendAnalyzer;

/**
 * NationalInsightsGenerator — Aggregates all national-level data into a single report.
 *
 * @package App\K1\Ai\National
 */
class NationalInsightsGenerator
{
    private SchoolBenchmarkEngine $benchmark;
    private CountyPerformanceAggregator $county;
    private EducationTrendAnalyzer $trends;

    public function __construct()
    {
        $this->benchmark = app(SchoolBenchmarkEngine::class);
        $this->county    = app(CountyPerformanceAggregator::class);
        $this->trends    = app(EducationTrendAnalyzer::class);
    }

    /**
     * Generate a comprehensive national intelligence report.
     *
     * @return array{summary: array, trends: array, benchmarks: array}
     */
    public function generate(): array
    {
        return [
            'summary'   => $this->benchmark->benchmark(),
            'counties'  => $this->county->aggregate(),
            'trends'    => $this->trends->analyze(),
            'generated_at' => now()->toIso8601String(),
        ];
    }
}