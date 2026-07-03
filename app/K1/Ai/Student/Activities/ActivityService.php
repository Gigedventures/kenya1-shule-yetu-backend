<?php

namespace App\K1\Ai\Student\Activities;

use App\Modules\ShuleYetu\Support\Tenancy\SchoolContext;

class ActivityService
{
    public function getClubs(string $schoolId): array
    {
        return DB::table('k1_clubs')
            ->where('school_id', $schoolId)
            ->orderBy('name')
            ->get()
            ->toArray();
    }

    public function join(string $studentId, string $clubId): array
    {
        DB::table('k1_club_members')->updateOrInsert(
            ['student_id' => $studentId, 'club_id' => $clubId],
            ['joined_at' => now()]
        );
        return ['joined' => true];
    }

    public function getHistory(string $studentId): array
    {
        return DB::table('k1_club_members')
            ->where('student_id', $studentId)
            ->get()
            ->toArray();
    }
}