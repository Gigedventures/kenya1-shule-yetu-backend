<?php

namespace App\Modules\ShuleYetu\Models;

use App\Models\User;
use App\Modules\ShuleYetu\Support\Models\BaseShuleModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShuleNotification extends BaseShuleModel
{
    protected $table = 'shule_notifications';

    protected $fillable = [
        'user_id',
        'type',
        'payload',
        'read_at',
    ];

    protected $casts = [
        'payload' => 'array',
        'read_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
