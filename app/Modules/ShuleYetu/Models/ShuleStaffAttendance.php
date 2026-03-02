<?php

namespace App\Modules\ShuleYetu\Models;

use App\Modules\ShuleYetu\Support\Models\BaseShuleModel;
use App\Modules\ShuleYetu\Support\Tenancy\SchoolContext;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class ShuleStaffAttendance extends BaseShuleModel
{
    protected $table = 'shule_staff_attendance';

    protected $fillable = [
        'staff_id',
        'attendance_date',
        'status',
        'remarks',
    ];

    protected $casts = [
        'attendance_date' => 'date',
    ];

    protected static function booted(): void
    {
        static::saving(function (ShuleStaffAttendance $attendance): void {
            $schoolId = $attendance->school_id ?: app(SchoolContext::class)->id();
            if (!$schoolId) {
                throw new RuntimeException('No active school context for staff attendance.');
            }

            $staffSchoolId = DB::table('shule_staff')
                ->where('id', $attendance->staff_id)
                ->value('school_id');

            if (!$staffSchoolId || $staffSchoolId !== $schoolId) {
                throw new RuntimeException('Staff attendance must belong to active school.');
            }
        });
    }

    public function staff(): BelongsTo
    {
        return $this->belongsTo(ShuleStaff::class, 'staff_id');
    }
}
