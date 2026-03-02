<?php

namespace App\Modules\ShuleYetu\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class School extends Model
{
    protected $table = 'schools';

    protected $fillable = [
        'uuid',
        'owner_user_id',
        'name',
        'code',
        'phone',
        'email',
        'location',
        'currency',
        'country',
        'status',
        'activated_at',
    ];

    protected $casts = [
        'activated_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->uuid)) {
                $model->uuid = (string) Str::uuid();
            }
        });
    }

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_user_id');
    }

    public function classes()
    {
        return $this->hasMany(ShuleClass::class, 'school_id');
    }
}
