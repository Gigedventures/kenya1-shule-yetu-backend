<?php

namespace App\K1\Ai\Parent;

use App\K1\Ai\Core\LearningLoop\AdaptiveWeightsEngine;
use Illuminate\Support\Facades\DB;

/**
 * HomeInterventionAdvisor — Suggests simple home-based learning activities.
 *
 * @package App\K1\Ai\Parent
 */
class HomeInterventionAdvisor
{
    /**
     * Suggest home learning activities.
     *
     * @param string $studentId
     * @return array{routine: string[], activities: string[], behaviors: string[]}
     */
    public function suggest(string $studentId): array
    {
        return [
            'routine'  => ['Study 20 min after school', 'Review notes before bed', 'Practice weak subjects on weekends'],
            'activities' => ['Read a book together', 'Solve simple maths problems', 'Write a short story'],
            'behaviors' => ['Praise effort, not results', 'Set a consistent study time', 'Limit screen time before study'],
        ];
    }
}