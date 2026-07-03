<?php

namespace App\K1\Ai\Student\LearningCenter;

use App\Modules\ShuleYetu\Support\Tenancy\SchoolContext;
use Illuminate\Support\Facades\DB;

class LearningCenterService
{
    public function getMaterials(string $gradeId, string $subjectId = null): array
    {
        $q = DB::table('k1_learning_materials')->where('grade_id', $gradeId);
        if ($subjectId) $q->where('subject_id', $subjectId);
        return $q->orderByDesc('created_at')->get()->toArray();
    }

    public function bookmark(string $materialId, string $studentId): array
    {
        DB::table('k1_bookmarks')->updateOrInsert(
            ['material_id' => $materialId, 'student_id' => $studentId],
            ['created_at' => now()]
        );
        return ['bookmarked' => true];
    }

    public function search(string $q): array
    {
        return DB::table('k1_learning_materials')
            ->where('title', 'like', "%{$q}%")
            ->orWhere('description', 'like', "%{$q}%")
            ->get()
            ->toArray();
    }
}