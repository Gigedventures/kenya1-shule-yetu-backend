<?php

namespace App\Modules\ShuleYetu\Models;

use App\Modules\ShuleYetu\Support\Models\BaseShuleModel;
use App\Modules\ShuleYetu\Support\Tenancy\SchoolContext;
use RuntimeException;

class ShuleGradeBand extends BaseShuleModel
{
    protected $table = 'shule_grade_bands';

    protected $fillable = [
        'min_percentage',
        'max_percentage',
        'grade',
        'remarks',
    ];

    protected $casts = [
        'min_percentage' => 'decimal:2',
        'max_percentage' => 'decimal:2',
    ];

    protected static function booted(): void
    {
        static::saving(function (ShuleGradeBand $band): void {
            $schoolId = $band->school_id ?: app(SchoolContext::class)->id();
            if (!$schoolId) {
                throw new RuntimeException('No active school context for grade band.');
            }

            if ($band->min_percentage > $band->max_percentage) {
                throw new RuntimeException('Grade band minimum cannot exceed maximum.');
            }
        });
    }
}
