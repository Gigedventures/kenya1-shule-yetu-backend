<?php

namespace App\K1\Ai\TeacherPortal\Collaboration;

use App\Modules\ShuleYetu\Support\Tenancy\SchoolContext;
use Illuminate\Support\Facades\DB;

class CommunicationService
{
    // 1. Parent messaging
    public function sendParentMessage(string $parentId, string $studentId, string $message): array
    {
        DB::table('k1_messages')->insert([
            'from' => 'teacher', 'to' => $parentId, 'student_id' => $studentId,
            'body' => $message, 'type' => 'parent_update', 'created_at' => now(),
        ]);
        return ['sent' => true];
    }

    // 2. Class broadcast
    public function broadcast(string $classId, string $message): array
    {
        DB::table('k1_announcements')->insert([
            'class_id' => $classId, 'body' => $message,
            'type' => 'broadcast', 'created_at' => now(),
        ]);
        return ['broadcast' => true];
    }

    // 3. AI notices
    public function autoNotice(string $classId): array
    {
        $avg = DB::table('k1_lesson_outcomes')->where('class_id', $classId)->avg('effectiveness') ?? 50;
        $notice = $avg < 40 ? "Class performance dropping — review required" : "Class on track — maintain pace";
        return ['notice' => $notice, 'class_id' => $classId];
    }

    // 4. Meeting scheduler
    public function scheduleMeeting(array $data): array
    {
        DB::table('k1_meetings')->insert($data + ['created_at' => now()]);
        return ['scheduled' => true];
    }

    // 5. Announcement templates
    public function template(string $type): array
    {
        return match ($type) {
            'emergency' => ['title' => 'URGENT', 'body' => 'Please respond immediately'],
            'reminder' => ['title' => 'Reminder', 'body' => 'Upcoming deadline'],
            default => ['title' => 'Notice', 'body' => 'General announcement'],
        };
    }
}