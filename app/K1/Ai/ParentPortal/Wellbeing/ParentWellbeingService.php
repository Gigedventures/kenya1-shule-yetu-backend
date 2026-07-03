<?php

namespace App\K1\Ai\ParentPortal\Wellbeing;

use App\K1\Ai\Student\Wellbeing\WellbeingService;

class ParentWellbeingService
{
    private WellbeingService $wb;

    public function __construct()
    {
        $this->wb = app(WellbeingService::class);
    }

    public function getDashboard(string $studentId): array
    {
        return ['student_id' => $studentId, 'wellness_score' => 85, 'counseling_available' => true];
    }
}