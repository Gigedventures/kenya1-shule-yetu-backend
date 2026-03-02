<?php

namespace App\Modules\ShuleYetu\Models;

use App\Models\User;
use App\Modules\ShuleYetu\Support\Models\BaseShuleModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShuleMessage extends BaseShuleModel
{
    protected $table = 'shule_messages';

    protected $fillable = [
        'sender_user_id',
        'recipient_user_id',
        'subject',
        'body',
        'read_at',
    ];

    protected $casts = [
        'read_at' => 'datetime',
    ];

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_user_id');
    }

    public function recipient(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recipient_user_id');
    }
}
