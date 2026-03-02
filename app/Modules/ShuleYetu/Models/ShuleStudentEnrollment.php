<?php

namespace App\Modules\ShuleYetu\Models;

use App\Modules\ShuleYetu\Support\Models\BaseShuleModel;
use App\Modules\ShuleYetu\Support\Tenancy\SchoolContext;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class ShuleStudentEnrollment extends BaseShuleModel
{
    protected $table = 'shule_student_enrollments';

    protected $fillable = [
        'student_id',
        'academic_year_id',
        'class_id',
        'stream_id',
        'enrollment_date',
        'exit_date',
        'status',
        'remarks',
    ];

    protected $casts = [
        'enrollment_date' => 'date',
        'exit_date' => 'date',
    ];

    protected static function booted(): void
    {
        static::saving(function (ShuleStudentEnrollment $enrollment): void {
            $schoolId = $enrollment->school_id ?: app(SchoolContext::class)->id();
            if (!$schoolId) {
                throw new RuntimeException('No active school context for enrollment.');
            }

            $studentSchoolId = DB::table('shule_students')
                ->where('id', $enrollment->student_id)
                ->value('school_id');
            if (!$studentSchoolId || $studentSchoolId !== $schoolId) {
                throw new RuntimeException('Enrollment student must belong to active school.');
            }

            $academicYearSchoolId = DB::table('shule_academic_years')
                ->where('id', $enrollment->academic_year_id)
                ->value('school_id');
            if (!$academicYearSchoolId || $academicYearSchoolId !== $schoolId) {
                throw new RuntimeException('Enrollment academic year must belong to active school.');
            }

            if ($enrollment->class_id) {
                $classSchoolId = DB::table('shule_classes')
                    ->where('id', $enrollment->class_id)
                    ->value('school_id');
                if (!$classSchoolId || $classSchoolId !== $schoolId) {
                    throw new RuntimeException('Enrollment class must belong to active school.');
                }
            }

            if ($enrollment->stream_id) {
                $stream = DB::table('shule_streams')
                    ->where('id', $enrollment->stream_id)
                    ->first(['school_id', 'class_id']);
                if (!$stream || $stream->school_id !== $schoolId) {
                    throw new RuntimeException('Enrollment stream must belong to active school.');
                }
                if ($enrollment->class_id && $stream->class_id !== $enrollment->class_id) {
                    throw new RuntimeException('Enrollment stream must belong to selected class.');
                }
            }

            $duplicateExists = static::query()
                ->where('student_id', $enrollment->student_id)
                ->where('academic_year_id', $enrollment->academic_year_id)
                ->when($enrollment->exists, fn ($q) => $q->whereKeyNot($enrollment->getKey()))
                ->exists();

            if ($duplicateExists) {
                throw new RuntimeException('Student already has enrollment for this academic year.');
            }
        });
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(ShuleStudent::class, 'student_id');
    }

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(ShuleAcademicYear::class, 'academic_year_id');
    }

    public function class(): BelongsTo
    {
        return $this->belongsTo(ShuleClass::class, 'class_id');
    }

    public function stream(): BelongsTo
    {
        return $this->belongsTo(ShuleStream::class, 'stream_id');
    }
}

