<?php

namespace App\Modules\ShuleYetu\Models;

use App\Modules\ShuleYetu\Support\Models\BaseShuleModel;
use App\Modules\ShuleYetu\Support\Tenancy\SchoolContext;
use RuntimeException;

class ShuleFinancialYear extends BaseShuleModel
{
    protected $table = 'shule_financial_years';

    protected $fillable = [
        'name',
        'start_date',
        'end_date',
        'is_active',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'is_active' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::saving(function (self $year): void {
            if (!$year->is_active) {
                return;
            }

            $schoolId = $year->school_id ?: app(SchoolContext::class)->id();
            if (!$schoolId) {
                throw new RuntimeException('No active school context for financial year.');
            }

            $exists = self::query()
                ->where('school_id', $schoolId)
                ->where('is_active', true)
                ->when($year->exists, fn ($q) => $q->where('id', '!=', $year->id))
                ->exists();

            if ($exists) {
                throw new RuntimeException('Only one active financial year is allowed per school.');
            }
        });
    }
}
