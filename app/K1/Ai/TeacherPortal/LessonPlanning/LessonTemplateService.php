<?php

namespace App\K1\Ai\TeacherPortal\LessonPlanning;

use App\Modules\ShuleYetu\Support\Tenancy\SchoolContext;
use Illuminate\Support\Facades\DB;

class LessonTemplateService
{
    public function saveTemplate(array $data): array
    {
        $schoolId = app(SchoolContext::class)->requireId();
        $id = DB::table('k1_lesson_templates')->insertGetId([
            'school_id' => $schoolId,
            'name' => $data['name'],
            'subject' => $data['subject'],
            'grade' => $data['grade'],
            'structure' => json_encode($data['structure']),
            'version' => 1,
            'created_at' => now(),
        ]);
        return ['template_id' => (string) $id, 'version' => 1];
    }

    public function getTemplates(?string $subject = null, ?string $grade = null): array
    {
        $q = DB::table('k1_lesson_templates')->where('school_id', app(SchoolContext::class)->requireId());
        if ($subject) $q->where('subject', $subject);
        if ($grade) $q->where('grade', $grade);
        return $q->orderByDesc('version')->get()->toArray();
    }

    public function versionize(string $templateId): array
    {
        $t = DB::table('k1_lesson_templates')->find($templateId);
        if (!$t) throw new \RuntimeException('Template not found');
        $newId = DB::table('k1_lesson_templates')->insertGetId([
            'school_id' => $t->school_id, 'name' => $t->name, 'subject' => $t->subject,
            'grade' => $t->grade, 'structure' => $t->structure, 'version' => $t->version + 1,
            'created_at' => now(),
        ]);
        return ['template_id' => (string) $newId, 'version' => $t->version + 1];
    }

    public function reuse(string $templateId, string $newClassId): array
    {
        $t = DB::table('k1_lesson_templates')->find($templateId);
        return $this->saveTemplate([
            'name' => $t->name . ' (reused)', 'subject' => $t->subject,
            'grade' => $newClassId, 'structure' => json_decode($t->structure, true),
        ]);
    }
}