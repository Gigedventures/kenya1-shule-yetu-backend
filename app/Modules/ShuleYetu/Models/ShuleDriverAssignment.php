<?php

namespace App\Modules\ShuleYetu\Models;

use App\Modules\ShuleYetu\Support\Models\BaseShuleModel;
use App\Modules\ShuleYetu\Support\Tenancy\SchoolContext;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class ShuleDriverAssignment extends BaseShuleModel
{
    protected $table = 'shule_driver_assignments';

    protected $fillable = [
        'vehicle_id',
        'staff_id',
        'start_date',
        'end_date',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    protected static function booted(): void
    {
        static::saving(function (self $assignment): void {
            $schoolId = $assignment->school_id ?: app(SchoolContext::class)->id();
            if (!$schoolId) {
                throw new RuntimeException('No active school context for driver assignment.');
            }

            $vehicleSchoolId = DB::table('shule_vehicles')
                ->where('id', $assignment->vehicle_id)
                ->value('school_id');
            if (!$vehicleSchoolId || $vehicleSchoolId !== $schoolId) {
                throw new RuntimeException('Driver assignment vehicle must belong to active school.');
            }

            $staffSchoolId = DB::table('shule_staff')
                ->where('id', $assignment->staff_id)
                ->value('school_id');
            if (!$staffSchoolId || $staffSchoolId !== $schoolId) {
                throw new RuntimeException('Driver assignment staff must belong to active school.');
            }
        });
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(ShuleVehicle::class, 'vehicle_id');
    }

    public function staff(): BelongsTo
    {
        return $this->belongsTo(ShuleStaff::class, 'staff_id');
    }
}
