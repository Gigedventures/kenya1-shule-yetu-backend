<?php

namespace App\Core\Finance;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use RuntimeException;

class K1LedgerEntry extends Model
{
    use HasUuids;

    protected $table = 'k1_ledger_entries';

    protected $fillable = [
        'tenant_id',
        'account_id',
        'reference_type',
        'reference_id',
        'debit',
        'credit',
        'description',
    ];

    protected $casts = [
        'debit' => 'decimal:2',
        'credit' => 'decimal:2',
    ];

    protected $keyType = 'string';

    public $incrementing = false;

    protected static function booted(): void
    {
        static::updating(function (): void {
            throw new RuntimeException('Ledger entries are append-only.');
        });

        static::deleting(function (): void {
            throw new RuntimeException('Ledger entries are append-only.');
        });
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(K1Account::class, 'account_id');
    }
}
