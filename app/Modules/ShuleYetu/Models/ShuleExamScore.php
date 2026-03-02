<?php

namespace App\Modules\ShuleYetu\Models;

use App\Models\User;
use App\Modules\ShuleYetu\Support\Models\BaseShuleModel;
use App\Modules\ShuleYetu\Support\Tenancy\SchoolContext;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class ShuleExamScore extends BaseShuleModel
{
    protected $table = 'shule_exam_scores';

    protected $fillable = [
        'exam_subject_id',
        'student_id',
        'marks_obtained',
        'percentage',
        'grade',
        'remarks',
        'entered_by_user_id',
    ];

    protected $casts = [
        'marks_obtained' => 'decimal:2',
        'percentage' => 'decimal:2',
    ];

    protected static function booted(): void
    {
        static::saving(function (ShuleExamScore $score): void {
            $schoolId = $score->school_id ?: app(SchoolContext::class)->id();
            if (!$schoolId) {
                throw new RuntimeException('No active school context for exam score.');
            }

            $examSubjectSchoolId = DB::table('shule_exam_subjects')
                ->where('id', $score->exam_subject_id)
                ->value('school_id');
            if (!$examSubjectSchoolId || $examSubjectSchoolId !== $schoolId) {
                throw new RuntimeException('Exam score must belong to active school.');
            }

            $studentSchoolId = DB::table('shule_students')
                ->where('id', $score->student_id)
                ->value('school_id');
            if (!$studentSchoolId || $studentSchoolId !== $schoolId) {
                throw new RuntimeException('Exam score student must belong to active school.');
            }
        });
    }

    public function examSubject(): BelongsTo
    {
        return $this->belongsTo(ShuleExamSubject::class, 'exam_subject_id');
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(ShuleStudent::class, 'student_id');
    }

    public function enteredBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'entered_by_user_id');
    }
}
