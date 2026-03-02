<?php

namespace App\Modules\ShuleYetu\Models;

use App\Modules\ShuleYetu\Support\Models\BaseShuleModel;
use App\Modules\ShuleYetu\Support\Tenancy\SchoolContext;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class ShuleFeeItem extends BaseShuleModel
{
    protected $table = 'shule_fee_items';

    protected $fillable = [
        'fee_structure_id',
        'name',
        'amount',
        'is_mandatory',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'is_mandatory' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::saving(function (ShuleFeeItem $item): void {
            $schoolId = $item->school_id ?: app(SchoolContext::class)->id();
            if (!$schoolId) {
                throw new RuntimeException('No active school context for fee item.');
            }

            $structureSchoolId = DB::table('shule_fee_structures')
                ->where('id', $item->fee_structure_id)
                ->value('school_id');
            if (!$structureSchoolId || $structureSchoolId !== $schoolId) {
                throw new RuntimeException('Fee item must belong to active school.');
            }
        });
    }

    public function feeStructure(): BelongsTo
    {
        return $this->belongsTo(ShuleFeeStructure::class, 'fee_structure_id');
    }
}
