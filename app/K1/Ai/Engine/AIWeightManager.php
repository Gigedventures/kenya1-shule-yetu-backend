<?php

namespace App\K1\Ai\Engine;

use App\K1\Ai\Core\LearningLoop\AdaptiveWeightsEngine;
use App\Modules\ShuleYetu\Support\Tenancy\SchoolContext;

/**
 * AIWeightManager — Manages dynamic AI generation weights per school.
 *
 * Stores and retrieves weight configurations that determine
 * how the AI system generates content for each school.
 *
 * @package App\K1\Ai\Engine
 */
class AIWeightManager
{
    private AdaptiveWeightsEngine $engine;

    public function __construct()
    {
        $this->engine = app(AdaptiveWeightsEngine::class);
    }

    /**
     * Get the current weight configuration for a school.
     */
    public function getWeights(string $schoolId, string $subject): array
    {
        return $this->engine->calculateWeights($subject, 'General', $schoolId);
    }

    /**
     * Apply weight adjustments to a generation request.
     */
    public function adjust(array $input, array $weights): array
    {
        foreach ($weights['adjustments'] ?? [] as $adjustment) {
            $input['_adjustments'][] = $adjustment;
        }

        $input['_weights'] = $weights['weights'] ?? [];

        return $input;
    }
}