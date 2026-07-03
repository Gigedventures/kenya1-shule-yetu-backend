<?php

namespace App\K1\Ai\Student\Attendance;

use App\Modules\ShuleYetu\Support\Tenancy\SchoolContext;

class AttendanceStudentService
{
    public function getHistory(string $studentId): array
    {
        $schoolId = app(SchoolContext::class)->requireId();
        $records = DB::table('k1_attendance')
            ->where('student_id', $studentId)
            ->orderByDesc('date')
            ->get()
            ->toArray();

        return [
            'records' => $records,
            'total' => count($records),
            'present' => collect($records)->where('status', 'present')->count(),
            'absent' => collect($records)->where('status', 'absent')->count(),
            'rate' => count($records) > 0 ? round(collect($records)->where('status', 'present')->count() / count($records) * 100, 1) : 0,
        ];
    }

    public function getTrend(string $studentId): array
    {
        $records = $this->getHistory($studentId);
        return ['trend' => $records['rate'] > 80 ? 'up' : ($records['rate'] > 50 ? 'stable' : 'down')];
    }
}