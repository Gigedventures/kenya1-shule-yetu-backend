<?php

namespace App\K1\Ai\Student\Wellbeing;

use App\Modules\ShuleYetu\Support\Tenancy\SchoolContext;

class WellbeingService
{
    public function request(string $studentId, array $data): array
    {
        DB::table('k1_wellbeing_requests')->insert([
            'student_id' => $studentId,
            'type' => $data['type'],
            'details' => $data['details'],
            'created_at' => now(),
            'status' => 'open',
        ]);
        return ['submitted' => true, 'message' => 'Support request received'];
    }

    public function getGuidance(string $schoolId): array
    {
        return DB::table('k1_guidance_resources')
            ->where('school_id', $schoolId)
            ->get()
            ->toArray();
    }
}