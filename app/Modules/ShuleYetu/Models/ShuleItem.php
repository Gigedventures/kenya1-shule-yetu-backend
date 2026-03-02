<?php

namespace App\Modules\ShuleYetu\Models;

use App\Modules\ShuleYetu\Support\Models\BaseShuleModel;
use App\Modules\ShuleYetu\Support\Tenancy\SchoolContext;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class ShuleItem extends BaseShuleModel
{
    protected $table = 'shule_items';

    protected $fillable = [
        'category_id',
        'sku',
        'name',
        'unit',
        'reorder_level',
    ];

    protected $casts = [
        'reorder_level' => 'decimal:3',
    ];

    protected static function booted(): void
    {
        static::saving(function (self $item): void {
            $schoolId = $item->school_id ?: app(SchoolContext::class)->id();
            if (!$schoolId) {
                throw new RuntimeException('No active school context for item.');
            }

            $categorySchoolId = DB::table('shule_item_categories')
                ->where('id', $item->category_id)
                ->value('school_id');
            if (!$categorySchoolId || $categorySchoolId !== $schoolId) {
                throw new RuntimeException('Item category must belong to active school.');
            }
        });
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ShuleItemCategory::class, 'category_id');
    }

    public function stockEntries(): HasMany
    {
        return $this->hasMany(ShuleStockEntry::class, 'item_id');
    }

    public function stockIssues(): HasMany
    {
        return $this->hasMany(ShuleStockIssue::class, 'item_id');
    }
}
