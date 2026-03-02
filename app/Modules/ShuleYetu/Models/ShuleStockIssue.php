<?php

namespace App\Modules\ShuleYetu\Models;

use App\Modules\ShuleYetu\Support\Models\BaseShuleModel;
use App\Modules\ShuleYetu\Support\Tenancy\SchoolContext;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class ShuleStockIssue extends BaseShuleModel
{
    protected $table = 'shule_stock_issues';

    protected $fillable = [
        'item_id',
        'qty',
        'issue_date',
        'issued_to',
        'status',
        'unit_cost',
        'total_cost',
        'posted_at',
    ];

    protected $casts = [
        'qty' => 'decimal:3',
        'issue_date' => 'date',
        'unit_cost' => 'decimal:2',
        'total_cost' => 'decimal:2',
        'posted_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::saving(function (self $issue): void {
            $schoolId = $issue->school_id ?: app(SchoolContext::class)->id();
            if (!$schoolId) {
                throw new RuntimeException('No active school context for stock issue.');
            }

            $itemSchoolId = DB::table('shule_items')
                ->where('id', $issue->item_id)
                ->value('school_id');
            if (!$itemSchoolId || $itemSchoolId !== $schoolId) {
                throw new RuntimeException('Stock issue item must belong to active school.');
            }
        });
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(ShuleItem::class, 'item_id');
    }
}
