<?php

namespace App\Modules\ShuleYetu\Models;

use App\Modules\ShuleYetu\Support\Models\BaseShuleModel;
use App\Modules\ShuleYetu\Support\Tenancy\SchoolContext;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class ShuleStudentGuardian extends BaseShuleModel
{
    protected $table = 'shule_student_guardian';

    protected $fillable = [
        'student_id',
        'guardian_id',
        'relationship',
        'is_primary',
    ];

    protected $casts = [
        'is_primary' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::saving(function (ShuleStudentGuardian $pivot): void {
            $schoolId = $pivot->school_id ?: app(SchoolContext::class)->id();
            if (!$schoolId) {
                throw new RuntimeException('No active school context for guardian link.');
            }

            $studentSchoolId = DB::table('shule_students')
                ->where('id', $pivot->student_id)
                ->value('school_id');
            $guardianSchoolId = DB::table('shule_guardians')
                ->where('id', $pivot->guardian_id)
                ->value('school_id');

            if (!$studentSchoolId || !$guardianSchoolId || $studentSchoolId !== $guardianSchoolId || $studentSchoolId !== $schoolId) {
                throw new RuntimeException('Guardian and student must belong to same school.');
            }
        });

        static::saved(function (ShuleStudentGuardian $pivot): void {
            if (!$pivot->is_primary) {
                return;
            }

            static::query()
                ->where('student_id', $pivot->student_id)
                ->whereKeyNot($pivot->getKey())
                ->update(['is_primary' => false]);
        });
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(ShuleStudent::class, 'student_id');
    }

    public function guardian(): BelongsTo
    {
        return $this->belongsTo(ShuleGuardian::class, 'guardian_id');
    }
}

