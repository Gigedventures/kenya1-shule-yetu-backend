<?php

namespace App\K1\Ai\Student\Homework;

use App\Modules\ShuleYetu\Support\Tenancy\SchoolContext;
use Carbon\Carbon;

class HomeworkService
{
    public function getDaily(string $studentId): array
    {
        return DB::table('k1_homework')
            ->where('student_id', $studentId)
            ->whereDate('due_date', today())
            ->get()
            ->toArray();
    }

    public function getWeekly(string $studentId): array
    {
        return DB::table('k1_homework')
            ->where('student_id', $studentId)
            ->whereBetween('due_date', [today(), today()->addDays(7)])
            ->get()
            ->toArray();
    }

    public function complete(string $homeworkId): array
    {
        DB::table('k1_homework')->where('id', $homeworkId)->update([
            'completed_at' => now(), 'status' => 'done',
        ]);
        return ['completed' => true, 'message' => 'Keep up the good work!'];
    }

    public function getStats(string $studentId): array
    {
        $total = DB::table('k1_homework')->where('student_id', $studentId)->count();
        $done = DB::table('k1_homework')->where('student_id', $studentId)->where('status', 'done')->count();
        return ['total' => $total, 'completed' => $done, 'rate' => $total > 0 ? round($done / $total * 100, 1) : 0];
    }
}