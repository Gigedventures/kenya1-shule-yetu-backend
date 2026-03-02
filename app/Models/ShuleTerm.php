<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ShuleTerm extends Model
{
    use HasFactory;

    protected $fillable = [
        'uuid',
        'school_id',
        'academic_year_id',
        'name',
        'starts_on',
        'ends_on',
        'is_active',
    ];

    protected $casts = [
        'starts_on' => 'date',
        'ends_on'   => 'date',
        'is_active' => 'boolean',
    ];
}
