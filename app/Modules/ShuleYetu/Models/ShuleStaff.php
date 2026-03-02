<?php

namespace App\Modules\ShuleYetu\Models;

use App\Models\User;
use App\Modules\ShuleYetu\Support\Models\BaseShuleModel;
use App\Modules\ShuleYetu\Support\Tenancy\SchoolContext;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class ShuleStaff extends BaseShuleModel
{
    protected $table = 'shule_staff';

    protected $fillable = [
        'user_id',
        'staff_no',
        'first_name',
        'last_name',
        'phone',
        'email',
        'gender',
        'staff_type',
        'department_id',
        'status',
        'joined_at',
        'left_at',
    ];

    protected $casts = [
        'joined_at' => 'date',
        'left_at' => 'date',
    ];

    protected static function booted(): void
    {
        static::saving(function (ShuleStaff $staff): void {
            $schoolId = $staff->school_id ?: app(SchoolContext::class)->id();
            if (!$schoolId) {
                throw new RuntimeException('No active school context for staff.');
            }

            if ($staff->department_id) {
                $departmentSchoolId = DB::table('shule_departments')
                    ->where('id', $staff->department_id)
                    ->value('school_id');

                if (!$departmentSchoolId || $departmentSchoolId !== $schoolId) {
                    throw new RuntimeException('Staff department must belong to active school.');
                }
            }
        });
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(ShuleDepartment::class, 'department_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function teacherAssignments(): HasMany
    {
        return $this->hasMany(ShuleTeacherAssignment::class, 'staff_id');
    }

    public function attendance(): HasMany
    {
        return $this->hasMany(ShuleStaffAttendance::class, 'staff_id');
    }
}
