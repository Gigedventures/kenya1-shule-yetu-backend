<?php

namespace App\Modules\ShuleYetu\Models;

use App\Modules\ShuleYetu\Support\Models\BaseShuleModel;
use App\Modules\ShuleYetu\Support\Tenancy\SchoolContext;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class ShuleStudent extends BaseShuleModel
{
    protected $table = 'shule_students';

    protected $fillable = [
        'admission_no',
        'first_name',
        'middle_name',
        'last_name',
        'gender',
        'dob',
        'nationality',
        'status',
        'current_class_id',
        'current_stream_id',
        'admission_date',
        'photo_path',
        'extra',
    ];

    protected $casts = [
        'dob' => 'date',
        'admission_date' => 'date',
        'extra' => 'array',
    ];

    protected static function booted(): void
    {
        static::saving(function (ShuleStudent $student): void {
            $schoolId = $student->school_id ?: app(SchoolContext::class)->id();
            if (!$schoolId) {
                throw new RuntimeException('No active school context for student.');
            }

            if ($student->current_class_id) {
                $classSchoolId = DB::table('shule_classes')
                    ->where('id', $student->current_class_id)
                    ->value('school_id');

                if (!$classSchoolId || $classSchoolId !== $schoolId) {
                    throw new RuntimeException('Student class must belong to same school.');
                }
            }

            if ($student->current_stream_id) {
                $stream = DB::table('shule_streams')
                    ->where('id', $student->current_stream_id)
                    ->first(['school_id', 'class_id']);

                if (!$stream || $stream->school_id !== $schoolId) {
                    throw new RuntimeException('Student stream must belong to same school.');
                }

                if ($student->current_class_id && $stream->class_id !== $student->current_class_id) {
                    throw new RuntimeException('Student stream must belong to selected class.');
                }
            }
        });

        static::updated(function (ShuleStudent $student): void {
            if (!($student->wasChanged('current_class_id') || $student->wasChanged('current_stream_id'))) {
                return;
            }

            ShuleStudentClassHistory::query()->create([
                'student_id' => $student->id,
                'from_class_id' => $student->getOriginal('current_class_id'),
                'from_stream_id' => $student->getOriginal('current_stream_id'),
                'to_class_id' => $student->current_class_id,
                'to_stream_id' => $student->current_stream_id,
                'changed_at' => now(),
                'changed_by_user_id' => auth()->id(),
            ]);
        });
    }

    public function guardians(): BelongsToMany
    {
        return $this->belongsToMany(
            ShuleGuardian::class,
            'shule_student_guardian',
            'student_id',
            'guardian_id'
        )->withPivot(['id', 'school_id', 'relationship', 'is_primary'])->withTimestamps();
    }

    public function enrollments(): HasMany
    {
        return $this->hasMany(ShuleStudentEnrollment::class, 'student_id');
    }

    public function currentClass(): BelongsTo
    {
        return $this->belongsTo(ShuleClass::class, 'current_class_id');
    }

    public function currentStream(): BelongsTo
    {
        return $this->belongsTo(ShuleStream::class, 'current_stream_id');
    }

    public function classHistory(): HasMany
    {
        return $this->hasMany(ShuleStudentClassHistory::class, 'student_id');
    }
}

