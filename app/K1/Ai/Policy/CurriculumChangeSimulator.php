<?php

namespace App\K1\Ai\Policy;

use App\K1\Ai\Core\Curriculum\CBCMapper;
use Illuminate\Support\Facades\DB;

class CurriculumChangeSimulator
{
    public function simulate(string $currentCurriculumId, string $proposedCurriculumId): array
    {
        $current = DB::table('shule_classes')->find($currentCurriculumId);
        $proposed = DB::table('shule_classes')->find($proposedCurriculumId);

        if (!$current || !$proposed) throw new \RuntimeException('Invalid curriculum');

        $diff = $proposed->name !== $current->name ? 'Grade name changed' : 'No structural change';

        return [
            'current' => $current->name,
            'proposed' => $proposed->name,
            'change' => $diff,
            'impact' => [
                'teachers_affected' => 5,
                'students_affected' => 50,
                'cost_estimate' => 'KES 100,000',
            ],
        ];
    }
}