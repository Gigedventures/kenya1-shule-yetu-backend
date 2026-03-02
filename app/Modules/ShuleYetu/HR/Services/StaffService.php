<?php

namespace App\Modules\ShuleYetu\HR\Services;

use App\Modules\ShuleYetu\Models\ShuleTeacherAssignment;
use App\Modules\ShuleYetu\Support\Tenancy\SchoolContext;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class StaffService
{
    public function validateTeacherAssignment(ShuleTeacherAssignment $assignment): void
    {
        $schoolId = $assignment->school_id ?: app(SchoolContext::class)->id();
        if (!$schoolId) {
            throw new RuntimeException('No active school context for teacher assignment.');
        }

        $academicYear = $this->requireSameSchool('shule_academic_years', $assignment->academic_year_id, $schoolId, 'Academic year');
        $term = $this->requireSameSchool('shule_terms', $assignment->term_id, $schoolId, 'Term', ['academic_year_id']);
        $class = $this->requireSameSchool('shule_classes', $assignment->class_id, $schoolId, 'Class');
        $staff = $this->requireSameSchool('shule_staff', $assignment->staff_id, $schoolId, 'Staff', ['status']);

        if (!empty($term->academic_year_id) && $term->academic_year_id !== $academicYear->id) {
            throw new RuntimeException('Term must belong to selected academic year.');
        }

        if ($staff->status !== 'active') {
            throw new RuntimeException('Suspended or inactive staff cannot be assigned.');
        }

        if ($assignment->stream_id) {
            $stream = $this->requireSameSchool('shule_streams', $assignment->stream_id, $schoolId, 'Stream', ['class_id']);
            if ($stream->class_id !== $class->id) {
                throw new RuntimeException('Stream must belong to selected class.');
            }
        }

        if ($assignment->subject_id) {
            $subject = $this->requireSameSchool('shule_subjects', $assignment->subject_id, $schoolId, 'Subject');

            $allowed = DB::table('shule_class_subject')
                ->where('school_id', $schoolId)
                ->where('class_id', $class->id)
                ->where('subject_id', $subject->id)
                ->exists();

            if (!$allowed) {
                throw new RuntimeException('Selected subject is not allowed for this class.');
            }
        }

        if ($assignment->is_class_teacher) {
            $existing = DB::table('shule_teacher_assignments')
                ->where('school_id', $schoolId)
                ->where('term_id', $assignment->term_id)
                ->where('class_id', $assignment->class_id)
                ->when(
                    $assignment->stream_id,
                    fn ($q) => $q->where('stream_id', $assignment->stream_id),
                    fn ($q) => $q->whereNull('stream_id')
                )
                ->where('is_class_teacher', true)
                ->when($assignment->exists, fn ($q) => $q->where('id', '!=', $assignment->getKey()))
                ->exists();

            if ($existing) {
                throw new RuntimeException('Only one class teacher is allowed per class/stream per term.');
            }
        }
    }

    private function requireSameSchool(
        string $table,
        ?string $id,
        string $schoolId,
        string $label,
        array $columns = []
    ) {
        if (!$id) {
            throw new RuntimeException("{$label} is required.");
        }

        $record = DB::table($table)
            ->where('id', $id)
            ->when(!empty($columns), fn ($q) => $q->select(array_merge(['id', 'school_id'], $columns)))
            ->first();

        if (!$record || $record->school_id !== $schoolId) {
            throw new RuntimeException("{$label} must belong to active school.");
        }

        return $record;
    }
}
