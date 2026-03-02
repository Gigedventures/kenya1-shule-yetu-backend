<?php

namespace App\Modules\ShuleYetu\Models;

use App\Models\User;
use App\Modules\ShuleYetu\Support\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class ShuleRole extends Model
{
    use HasUuid;

    protected $table = 'shule_roles';

    protected $fillable = [
        'school_id',
        'name',
        'guard_name',
    ];

    protected $keyType = 'string';

    public $incrementing = false;

    public function school(): BelongsTo
    {
        return $this->belongsTo(ShuleSchool::class, 'school_id');
    }

    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(
            ShulePermission::class,
            'shule_role_has_permissions',
            'role_id',
            'permission_id'
        )->withTimestamps();
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(
            User::class,
            'shule_model_has_roles',
            'role_id',
            'user_id'
        )->withPivot('school_id')->withTimestamps();
    }
}

