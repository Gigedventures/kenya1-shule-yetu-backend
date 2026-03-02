<?php

namespace App\Modules\ShuleYetu\Models;

use App\Modules\ShuleYetu\Support\Models\BaseShuleModel;
use App\Modules\ShuleYetu\Support\Tenancy\SchoolContext;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class ShuleExam extends BaseShuleModel
{
    protected $table = 'shule_exams';

    protected $fillable = [
        'academic_year_id',
        'term_id',
        'exam_type_id',
        'class_id',
        'stream_id',
        'title',
        'total_marks',
        'start_date',
        'end_date',
        'status',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    protected static function booted(): void
    {
        static::saving(function (ShuleExam $exam): void {
            $schoolId = $exam->school_id ?: app(SchoolContext::class)->id();
            if (!$schoolId) {
                throw new RuntimeException('No active school context for exam.');
            }

            $academicYearSchoolId = DB::table('shule_academic_years')
                ->where('id', $exam->academic_year_id)
                ->value('school_id');
            if (!$academicYearSchoolId || $academicYearSchoolId !== $schoolId) {
                throw new RuntimeException('Exam academic year must belong to active school.');
            }

            $term = DB::table('shule_terms')
                ->where('id', $exam->term_id)
                ->first(['school_id', 'academic_year_id']);
            if (!$term || $term->school_id !== $schoolId) {
                throw new RuntimeException('Exam term must belong to active school.');
            }
            if ($term->academic_year_id !== $exam->academic_year_id) {
                throw new RuntimeException('Exam term must belong to selected academic year.');
            }

            $examTypeSchoolId = DB::table('shule_exam_types')
                ->where('id', $exam->exam_type_id)
                ->value('school_id');
            if (!$examTypeSchoolId || $examTypeSchoolId !== $schoolId) {
                throw new RuntimeException('Exam type must belong to active school.');
            }

            $classSchoolId = DB::table('shule_classes')
                ->where('id', $exam->class_id)
                ->value('school_id');
            if (!$classSchoolId || $classSchoolId !== $schoolId) {
                throw new RuntimeException('Exam class must belong to active school.');
            }

            if ($exam->stream_id) {
                $stream = DB::table('shule_streams')
                    ->where('id', $exam->stream_id)
                    ->first(['school_id', 'class_id']);
                if (!$stream || $stream->school_id !== $schoolId) {
                    throw new RuntimeException('Exam stream must belong to active school.');
                }
                if ($stream->class_id !== $exam->class_id) {
                    throw new RuntimeException('Exam stream must belong to selected class.');
                }
            }
        });
    }

    public function examType(): BelongsTo
    {
        return $this->belongsTo(ShuleExamType::class, 'exam_type_id');
    }

    public function subjects(): HasMany
    {
        return $this->hasMany(ShuleExamSubject::class, 'exam_id');
    }

    public function scoresThroughSubjects(): HasManyThrough
    {
        return $this->hasManyThrough(
            ShuleExamScore::class,
            ShuleExamSubject::class,
            'exam_id',
            'exam_subject_id',
            'id',
            'id'
        );
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

    public function stream(): BelongsTo
    {
        return $this->belongsTo(ShuleStream::class, 'stream_id');
    }
}
