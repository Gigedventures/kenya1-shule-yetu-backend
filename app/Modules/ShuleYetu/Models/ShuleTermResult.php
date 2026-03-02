<?php

namespace App\Modules\ShuleYetu\Models;

use App\Modules\ShuleYetu\Support\Models\BaseShuleModel;
use App\Modules\ShuleYetu\Support\Tenancy\SchoolContext;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class ShuleTermResult extends BaseShuleModel
{
    protected $table = 'shule_term_results';

    protected $fillable = [
        'academic_year_id',
        'term_id',
        'student_id',
        'total_marks',
        'total_percentage',
        'average',
        'overall_grade',
        'rank',
    ];

    protected $casts = [
        'total_marks' => 'decimal:2',
        'total_percentage' => 'decimal:2',
        'average' => 'decimal:2',
    ];

    protected static function booted(): void
    {
        static::saving(function (ShuleTermResult $result): void {
            $schoolId = $result->school_id ?: app(SchoolContext::class)->id();
            if (!$schoolId) {
                throw new RuntimeException('No active school context for term result.');
            }

            $academicYearSchoolId = DB::table('shule_academic_years')
                ->where('id', $result->academic_year_id)
                ->value('school_id');
            if (!$academicYearSchoolId || $academicYearSchoolId !== $schoolId) {
                throw new RuntimeException('Term result academic year must belong to active school.');
            }

            $termSchoolId = DB::table('shule_terms')
                ->where('id', $result->term_id)
                ->value('school_id');
            if (!$termSchoolId || $termSchoolId !== $schoolId) {
                throw new RuntimeException('Term result term must belong to active school.');
            }

            $studentSchoolId = DB::table('shule_students')
                ->where('id', $result->student_id)
                ->value('school_id');
            if (!$studentSchoolId || $studentSchoolId !== $schoolId) {
                throw new RuntimeException('Term result student must belong to active school.');
            }
        });
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(ShuleStudent::class, 'student_id');
    }

    public function term(): BelongsTo
    {
        return $this->belongsTo(ShuleTerm::class, 'term_id');
    }
}
