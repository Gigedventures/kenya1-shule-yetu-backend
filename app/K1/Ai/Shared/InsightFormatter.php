<?php

namespace App\K1\Ai\Shared;

use App\K1\Ai\Engine\OutputNormalizer;

/**
 * InsightFormatter — Final formatting layer for all AI output.
 *
 * All AI output MUST pass through this formatter before being returned to end users.
 *
 * @package App\K1\Ai\Shared
 */
class InsightFormatter
{
    private OutputNormalizer $normalizer;

    public function __construct()
    {
        $this->normalizer = new OutputNormalizer();
    }

    /**
     * Format a benchmark result for human consumption.
     */
    public function formatBenchmark(array $data): array
    {
        $data['_formatted'] = true;
        $data['_explanation'] = "Rankings are based on aggregate lesson outcomes. Higher scores = more effective teaching delivery.";
        return $data;
    }

    /**
     * Format a parent-friendly report.
     */
    public function formatParentReport(array $data): array
    {
        $data['_is_parent_friendly'] = true;
        $data['_language_level'] = 'simple';
        return $data;
    }
}