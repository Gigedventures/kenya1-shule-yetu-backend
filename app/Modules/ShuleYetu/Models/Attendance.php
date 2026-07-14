<?php

namespace App\Modules\ShuleYetu\Models;

use App\Modules\ShuleYetu\Support\Models\BaseShuleModel;
use App\Modules\ShuleYetu\Support\Tenancy\SchoolContext;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class Attendance extends BaseShuleModel
{
    protected $table = 'attendance';

    protected $fillable = [
        'academic_year_id',
        'term_id',
        'student_id',
        'class_id',
        'stream_id',
        'subject_id',
        'marked_by',
        'attendance_date',
        'status',
        'check_in_time',
        'check_out_time',
        'notes',
        'metadata',
    ];

    protected $casts = [
        'attendance_date' => 'date',
        'check_in_time' => 'datetime:H:i',
        'check_out_time' => 'datetime:H:i',
        'metadata' => 'array',
    ];

    protected static function booted(): void
    {
        static::saving(function (Attendance $attendance): void {
            $schoolId = $attendance->school_id ?: app(SchoolContext::class)->id();
            if (!$schoolId) {
                throw new RuntimeException('No active school context for attendance.');
            }

            // Validate student belongs to school
            $studentSchoolId = DB::table('shule_students')
                ->where('id', $attendance->student_id)
                ->value('school_id');
            if (!$studentSchoolId || $studentSchoolId !== $schoolId) {
                throw new RuntimeException('Student must belong to active school.');
            }

            // Validate class belongs to school
            $classSchoolId = DB::table('shule_classes')
                ->where('id', $attendance->class_id)
                ->value('school_id');
            if (!$classSchoolId || $classSchoolId !== $schoolId) {
                throw new RuntimeException('Class must belong to active school.');
            }

            // Validate stream belongs to class if provided
            if ($attendance->stream_id) {
                $stream = DB::table('shule_streams')
                    ->where('id', $attendance->stream_id)
                    ->first(['school_id', 'class_id']);
                if (!$stream || $stream->school_id !== $schoolId) {
                    throw new RuntimeException('Stream must belong to active school.');
                }
                if ($stream->class_id !== $attendance->class_id) {
                    throw new RuntimeException('Stream must belong to selected class.');
                }
            }

            // Validate subject belongs to school if provided
            if ($attendance->subject_id) {
                $subjectSchoolId = DB::table('shule_subjects')
                    ->where('id', $attendance->subject_id)
                    ->value('school_id');
                if (!$subjectSchoolId || $subjectSchoolId !== $schoolId) {
                    throw new RuntimeException('Subject must belong to active school.');
                }
            }

            // Validate marked_by user has access to school
            $markerSchoolId = DB::table('shule_staff')
                ->where('user_id', $attendance->marked_by)
                ->value('school_id');
            if (!$markerSchoolId || $markerSchoolId !== $schoolId) {
                // Check if user is admin with access
                $userSchools = DB::table('school_user')
                    ->where('user_id', $attendance->marked_by)
                    ->where('school_id', $schoolId)
                    ->exists();
                if (!$userSchools) {
                    throw new RuntimeException('Marker must have access to active school.');
                }
            }
        });
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(ShuleStudent::class, 'student_id');
    }

    public function class(): BelongsTo
    {
        return $this->belongsTo(ShuleClass::class, 'class_id');
    }

    public function stream(): BelongsTo
    {
        return $this->belongsTo(ShuleStream::class, 'stream_id');
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(ShuleSubject::class, 'subject_id');
    }

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(ShuleAcademicYear::class, 'academic_year_id');
    }

    public function term(): BelongsTo
    {
        return $this->belongsTo(ShuleTerm::class, 'term_id');
    }

    public function marker(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'marked_by');
    }

    public function scopeForStudent($query, int $studentId)
    {
        return $query->where('student_id', $studentId);
    }

    public function scopeForClass($query, int $classId)
    {
        return $query->where('class_id', $classId);
    }

    public function scopeForDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('attendance_date', [$startDate, $endDate]);
    }

    public function scopeForTerm($query, int $termId)
    {
        return $query->where('term_id', $termId);
    }

    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'present' => '#10B981',
            'absent' => '#EF4444',
            'late' => '#F59E0B',
            'excused' => '#3B82F6',
            default => '#6B7280',
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'present' => 'Present',
            'absent' => 'Absent',
            'late' => 'Late',
            'excused' => 'Excused',
            default => ucfirst($this->status),
        };
    }
}