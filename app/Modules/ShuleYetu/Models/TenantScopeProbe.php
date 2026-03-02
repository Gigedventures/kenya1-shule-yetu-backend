<?php

namespace App\Modules\ShuleYetu\Models;

use App\Modules\ShuleYetu\Support\Models\BaseShuleModel;

class TenantScopeProbe extends BaseShuleModel
{
    protected $table = 'shule_tenant_scope_probes';

    protected $fillable = [
        'school_id',
        'name',
    ];
}

