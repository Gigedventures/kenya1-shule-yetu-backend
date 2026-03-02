<?php

namespace App\Modules\ShuleYetu\Models;

use App\Models\User;
use App\Modules\ShuleYetu\Support\Models\BaseShuleModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use RuntimeException;

class ShuleReceipt extends BaseShuleModel
{
    protected $table = 'shule_receipts';

    protected $fillable = [
        'payment_id',
        'receipt_number',
        'issued_at',
        'issued_by',
    ];

    protected $casts = [
        'issued_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::updating(function (): void {
            throw new RuntimeException('Receipts are immutable.');
        });

        static::deleting(function (): void {
            throw new RuntimeException('Receipts are immutable.');
        });
    }

    public function payment(): BelongsTo
    {
        return $this->belongsTo(ShulePayment::class, 'payment_id');
    }

    public function issuedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'issued_by');
    }
}
