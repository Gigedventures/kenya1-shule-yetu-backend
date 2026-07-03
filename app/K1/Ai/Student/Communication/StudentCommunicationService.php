<?php

namespace App\K1\Ai\Student\Communication;

use App\Modules\ShuleYetu\Support\Tenancy\SchoolContext;

class StudentCommunicationService
{
    public function getMessages(string $studentId): array
    {
        return DB::table('k1_messages')
            ->where('to', $studentId)
            ->orWhere('student_id', $studentId)
            ->orderByDesc('created_at')
            ->get()
            ->toArray();
    }

    public function getAnnouncements(string $schoolId): array
    {
        return DB::table('k1_announcements')
            ->where('school_id', $schoolId)
            ->orderByDesc('created_at')
            ->get()
            ->toArray();
    }
}