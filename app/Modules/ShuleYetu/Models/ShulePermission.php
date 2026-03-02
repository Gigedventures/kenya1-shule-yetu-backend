<?php

namespace App\Modules\ShuleYetu\Models;

use App\Modules\ShuleYetu\Support\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class ShulePermission extends Model
{
    use HasUuid;

    protected $table = 'shule_permissions';

    protected $fillable = [
        'name',
        'guard_name',
    ];

    protected $keyType = 'string';

    public $incrementing = false;

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(
            ShuleRole::class,
            'shule_role_has_permissions',
            'permission_id',
            'role_id'
        )->withTimestamps();
    }
}

