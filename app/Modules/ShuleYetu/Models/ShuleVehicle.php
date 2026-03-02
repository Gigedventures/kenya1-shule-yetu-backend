<?php

namespace App\Modules\ShuleYetu\Models;

use App\Modules\ShuleYetu\Support\Models\BaseShuleModel;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ShuleVehicle extends BaseShuleModel
{
    protected $table = 'shule_vehicles';

    protected $fillable = [
        'plate_no',
        'capacity',
        'status',
    ];

    public function driverAssignments(): HasMany
    {
        return $this->hasMany(ShuleDriverAssignment::class, 'vehicle_id');
    }
}
