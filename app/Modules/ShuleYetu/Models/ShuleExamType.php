<?php

namespace App\Modules\ShuleYetu\Models;

use App\Modules\ShuleYetu\Support\Models\BaseShuleModel;

class ShuleExamType extends BaseShuleModel
{
    protected $table = 'shule_exam_types';

    protected $fillable = [
        'name',
        'weight',
        'is_active',
    ];

    protected $casts = [
        'weight' => 'decimal:2',
        'is_active' => 'boolean',
    ];
}
