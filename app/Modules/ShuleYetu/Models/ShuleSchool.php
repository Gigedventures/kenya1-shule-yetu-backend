<?php

namespace App\Modules\ShuleYetu\Models;

use App\Modules\ShuleYetu\Support\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShuleSchool extends Model
{
    use HasFactory;
    use HasUuid;

    protected $table = 'shule_schools';

    protected $fillable = [
        'name',
        'code',
        'status',
    ];

    protected $keyType = 'string';

    public $incrementing = false;
}
