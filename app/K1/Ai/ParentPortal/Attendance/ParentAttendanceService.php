<?php

namespace App\K1\Ai\ParentPortal\Attendance;

use App\K1\Ai\Student\Attendance\AttendanceStudentService;

class ParentAttendanceService
{
    private AttendanceStudentService $att;

    public function __construct()
    {
        $this->att = app(AttendanceStudentService::class);
    }

    public function getSummary(string $studentId): array
    {
        return $this->att->getHistory($studentId);
    }
}