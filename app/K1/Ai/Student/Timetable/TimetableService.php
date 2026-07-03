<?php

namespace App\K1\Ai\Student\Timetable;

use App\Modules\ShuleYetu\Support\Tenancy\SchoolContext;

class TimetableService
{
    public function getClassSchedule(string $classId): array
    {
        $schoolId = app(SchoolContext::class)->requireId();
        $schedule = DB::table('shule_subjects')
            ->where('school_id', $schoolId)
            ->get()
            ->toArray();

        return [
            'subjects' => array_map(fn($s) => [
                'subject' => $s->name,
                'time' => '09:00 - 10:00',
                'room' => 'Room ' . ($s->id % 10 + 1),
            ], $schedule),
        ];
    }

    public function getExamSchedule(string $termId): array
    {
        return DB::table('shule_exams')
            ->where('term_id', $termId)
            ->orderBy('start_date')
            ->get()
            ->toArray();
    }
}