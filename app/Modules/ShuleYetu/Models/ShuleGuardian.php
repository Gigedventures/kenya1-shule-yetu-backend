<?php

namespace App\Modules\ShuleYetu\Models;

use App\Modules\ShuleYetu\Support\Models\BaseShuleModel;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class ShuleGuardian extends BaseShuleModel
{
    protected $table = 'shule_guardians';

    protected $fillable = [
        'first_name',
        'last_name',
        'phone',
        'email',
        'id_number',
        'address',
        'extra',
    ];

    protected $casts = [
        'extra' => 'array',
    ];

    public function students(): BelongsToMany
    {
        return $this->belongsToMany(
            ShuleStudent::class,
            'shule_student_guardian',
            'guardian_id',
            'student_id'
        )->withPivot(['id', 'school_id', 'relationship', 'is_primary'])->withTimestamps();
    }
}

