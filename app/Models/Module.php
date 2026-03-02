<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Module extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'is_active',
        'description',
        'coming_soon_message',
        'image',
        'display_order',
    ];
}
