<?php

namespace App\K1\Ai\TeacherPortal\Classroom;

use App\Modules\ShuleYetu\Support\Tenancy\SchoolContext;
use Illuminate\Support\Facades\DB;

class ClassroomServiceProvider
{
    // 1. Live attendance
    public function trackAttendance(string $classId, array $students): array
    {
        $schoolId = app(SchoolContext::class)->requireId();
        $ids = [];
        foreach ($students as $s) {
            $ids[] = DB::table('k1_attendance')->insertGetId([
                'school_id' => $schoolId, 'class_id' => $classId,
                'student_id' => $s['student_id'], 'status' => $s['status'],
                'date' => now()->toDateString(),
            ]);
        }
        return ['recorded' => count($ids), 'ids' => $ids];
    }

    // 2. Engagement scoring
    public function scoreEngagement(string $studentId): array
    {
        $att = DB::table('k1_attendance')->where('student_id', $studentId)->count();
        return ['student_id' => $studentId, 'engagement_score' => min(100, (int)($att * 10)), 'trend' => 'stable'];
    }

    // 3. Behavioral notes
    public function addNote(string $studentId, string $note): array
    {
        DB::table('k1_behaviour_notes')->insert([
            'student_id' => $studentId, 'note' => $note, 'created_at' => now(),
        ]);
        return ['logged' => true];
    }

    // 4. Seating planner
    public function planSeating(string $classId): array
    {
        $students = DB::table('shule_students')->where('current_class_id', $classId)->get()->toArray();
        return ['class' => $classId, 'seating' => array_chunk($students, 4), 'rows' => ceil(count($students) / 4)];
    }

    // 5. Grouping engine
    public function groupStudents(string $classId, int $groupSize): array
    {
        $students = DB::table('shule_students')->where('current_class_id', $classId)->get()->toArray();
        $groups = array_chunk($students, $groupSize);
        return ['groups' => $groups, 'total_groups' => count($groups)];
    }

    // 6. Pace controller
    public function suggestPace(string $subject): array
    {
        $avg = DB::table('k1_lesson_outcomes')->where('subject', $subject)->avg('effectiveness') ?? 50;
        return ['subject' => $subject, 'pace' => $avg > 70 ? 'fast' : ($avg > 40 ? 'normal' : 'slow')];
    }
}