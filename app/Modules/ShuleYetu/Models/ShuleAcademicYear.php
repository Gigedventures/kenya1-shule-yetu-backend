<?php

namespace App\Modules\ShuleYetu\Models;

use App\Modules\ShuleYetu\Support\Models\BaseShuleModel;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ShuleAcademicYear extends BaseShuleModel
{
    protected $table = 'shule_academic_years';

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
        static::saved(function (ShuleAcademicYear $academicYear): void {
            if (!$academicYear->is_active) {
                return;
            }

            static::query()
                ->whereKeyNot($academicYear->getKey())
                ->update(['is_active' => false]);
        });
    }

    public function terms(): HasMany
    {
        return $this->hasMany(ShuleTerm::class, 'academic_year_id');
    }
}

