<?php

namespace App\Modules\ShuleYetu\Models;

use App\Modules\ShuleYetu\Support\Models\BaseShuleModel;
use App\Modules\ShuleYetu\Support\Tenancy\SchoolContext;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class ShuleStockEntry extends BaseShuleModel
{
    protected $table = 'shule_stock_entries';

    protected $fillable = [
        'item_id',
        'vendor_id',
        'qty',
        'unit_cost',
        'entry_date',
        'status',
        'posted_at',
    ];

    protected $casts = [
        'qty' => 'decimal:3',
        'unit_cost' => 'decimal:2',
        'entry_date' => 'date',
        'posted_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::saving(function (self $entry): void {
            $schoolId = $entry->school_id ?: app(SchoolContext::class)->id();
            if (!$schoolId) {
                throw new RuntimeException('No active school context for stock entry.');
            }

            $itemSchoolId = DB::table('shule_items')
                ->where('id', $entry->item_id)
                ->value('school_id');
            if (!$itemSchoolId || $itemSchoolId !== $schoolId) {
                throw new RuntimeException('Stock entry item must belong to active school.');
            }

            if ($entry->vendor_id) {
                $vendorSchoolId = DB::table('shule_vendors')
                    ->where('id', $entry->vendor_id)
                    ->value('school_id');
                if (!$vendorSchoolId || $vendorSchoolId !== $schoolId) {
                    throw new RuntimeException('Stock entry vendor must belong to active school.');
                }
            }
        });
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(ShuleItem::class, 'item_id');
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(ShuleVendor::class, 'vendor_id');
    }
}
