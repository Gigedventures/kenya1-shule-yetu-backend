<?php

namespace App\Modules\ShuleYetu\Models;

use App\Modules\ShuleYetu\Support\Models\BaseShuleModel;
use App\Modules\ShuleYetu\Support\Tenancy\SchoolContext;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class ShuleFeeStructure extends BaseShuleModel
{
    protected $table = 'shule_fee_structures';

    protected $fillable = [
        'academic_year_id',
        'term_id',
        'class_id',
        'name',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::saving(function (ShuleFeeStructure $structure): void {
            $schoolId = $structure->school_id ?: app(SchoolContext::class)->id();
            if (!$schoolId) {
                throw new RuntimeException('No active school context for fee structure.');
            }

            $academicYearSchoolId = DB::table('shule_academic_years')
                ->where('id', $structure->academic_year_id)
                ->value('school_id');
            if (!$academicYearSchoolId || $academicYearSchoolId !== $schoolId) {
                throw new RuntimeException('Fee structure academic year must belong to active school.');
            }

            $term = DB::table('shule_terms')
                ->where('id', $structure->term_id)
                ->first(['school_id', 'academic_year_id']);
            if (!$term || $term->school_id !== $schoolId) {
                throw new RuntimeException('Fee structure term must belong to active school.');
            }
            if ($term->academic_year_id !== $structure->academic_year_id) {
                throw new RuntimeException('Fee structure term must belong to selected academic year.');
            }

            $classSchoolId = DB::table('shule_classes')
                ->where('id', $structure->class_id)
                ->value('school_id');
            if (!$classSchoolId || $classSchoolId !== $schoolId) {
                throw new RuntimeException('Fee structure class must belong to active school.');
            }
        });
    }

    public function items(): HasMany
    {
        return $this->hasMany(ShuleFeeItem::class, 'fee_structure_id');
    }

    public function bills(): HasMany
    {
        return $this->hasMany(ShuleStudentBill::class, 'fee_structure_id');
    }

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(ShuleAcademicYear::class, 'academic_year_id');
    }

    public function term(): BelongsTo
    {
        return $this->belongsTo(ShuleTerm::class, 'term_id');
    }

    public function class(): BelongsTo
    {
        return $this->belongsTo(ShuleClass::class, 'class_id');
    }
}
