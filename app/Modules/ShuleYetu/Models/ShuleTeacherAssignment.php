<?php

namespace App\Modules\ShuleYetu\Models;

use App\Modules\ShuleYetu\HR\Services\StaffService;
use App\Modules\ShuleYetu\Support\Models\BaseShuleModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShuleTeacherAssignment extends BaseShuleModel
{
    protected $table = 'shule_teacher_assignments';

    protected $fillable = [
        'academic_year_id',
        'term_id',
        'staff_id',
        'class_id',
        'stream_id',
        'subject_id',
        'is_class_teacher',
    ];

    protected $casts = [
        'is_class_teacher' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::saving(function (ShuleTeacherAssignment $assignment): void {
            app(StaffService::class)->validateTeacherAssignment($assignment);
        });
    }

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(ShuleAcademicYear::class, 'academic_year_id');
    }

    public function term(): BelongsTo
    {
        return $this->belongsTo(ShuleTerm::class, 'term_id');
    }

    public function staff(): BelongsTo
    {
        return $this->belongsTo(ShuleStaff::class, 'staff_id');
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
}
