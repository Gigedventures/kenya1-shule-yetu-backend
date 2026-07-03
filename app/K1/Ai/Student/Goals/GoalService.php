<?php

namespace App\K1\Ai\Student\Goals;

use App\K1\Ai\Services\StudentPerformancePredictor;
use App\Modules\ShuleYetu\Support\Tenancy\SchoolContext;

class GoalService
{
    public function setGoal(string $studentId, array $data): array
    {
        DB::table('k1_student_goals')->insert([
            'student_id' => $studentId,
            'type' => $data['type'],
            'target' => $data['target'],
            'deadline' => $data['deadline'],
            'created_at' => now(),
        ]);
        return ['set' => true];
    }

    public function trackProgress(string $studentId): array
    {
        $predictor = app(StudentPerformancePredictor::class);
        $prediction = $predictor->predict($studentId);

        return [
            'predicted_average' => $prediction['predicted_average'],
            'goal' => 'Reach 70%',
            'progress' => round($prediction['predicted_average'] / 70 * 100, 1),
        ];
    }
}