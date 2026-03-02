<?php

namespace App\Core\Finance;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use RuntimeException;

class K1Account extends Model
{
    use HasUuids;

    protected $table = 'k1_accounts';

    protected $fillable = [
        'tenant_id',
        'code',
        'name',
        'type',
        'parent_id',
        'is_system',
    ];

    protected $casts = [
        'is_system' => 'boolean',
    ];

    protected $keyType = 'string';

    public $incrementing = false;

    protected static function booted(): void
    {
        static::updating(function (K1Account $account): void {
            if ($account->is_system) {
                throw new RuntimeException('System accounts are immutable.');
            }
        });
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }
}
