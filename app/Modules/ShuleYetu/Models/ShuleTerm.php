<?php

namespace App\Modules\ShuleYetu\Models;

use App\Modules\ShuleYetu\Support\Models\BaseShuleModel;
use App\Modules\ShuleYetu\Support\Tenancy\SchoolContext;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class ShuleTerm extends BaseShuleModel
{
    protected $table = 'shule_terms';

    protected $fillable = [
        'academic_year_id',
        'name',
        'start_date',
        'end_date',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    protected static function booted(): void
    {
        static::saving(function (ShuleTerm $term): void {
            $activeSchoolId = $term->school_id ?: app(SchoolContext::class)->id();
            if (empty($activeSchoolId)) {
                throw new RuntimeException('No active school context for term.');
            }

            $academicYearSchoolId = DB::table('shule_academic_years')
                ->where('id', $term->academic_year_id)
                ->value('school_id');

            if (empty($academicYearSchoolId) || $academicYearSchoolId !== $activeSchoolId) {
                throw new RuntimeException('Term and academic year must belong to same school.');
            }
        });
    }

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(ShuleAcademicYear::class, 'academic_year_id');
    }
}

