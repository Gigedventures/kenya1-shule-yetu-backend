<?php

namespace App\Modules\ShuleYetu\Models;

use App\Modules\ShuleYetu\Support\Models\BaseShuleModel;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ShuleDepartment extends BaseShuleModel
{
    protected $table = 'shule_departments';

    protected $fillable = [
        'name',
        'code',
    ];

    public function staff(): HasMany
    {
        return $this->hasMany(ShuleStaff::class, 'department_id');
    }
}
