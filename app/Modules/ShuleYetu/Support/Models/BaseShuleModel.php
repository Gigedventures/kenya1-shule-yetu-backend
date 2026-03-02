<?php

namespace App\Modules\ShuleYetu\Support\Models;

use App\Modules\ShuleYetu\Support\Traits\BelongsToSchool;
use App\Modules\ShuleYetu\Support\Traits\HasUuid;
use App\Modules\ShuleYetu\Support\Traits\ScopedToSchool;
use Illuminate\Database\Eloquent\Model;

abstract class BaseShuleModel extends Model
{
    use HasUuid;
    use BelongsToSchool;
    use ScopedToSchool;

    protected $guarded = [];

    protected $keyType = 'string';

    public $incrementing = false;
}
