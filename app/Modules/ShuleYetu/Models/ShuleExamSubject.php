<?php

namespace App\Modules\ShuleYetu\Models;

use App\Modules\ShuleYetu\Support\Models\BaseShuleModel;
use App\Modules\ShuleYetu\Support\Tenancy\SchoolContext;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class ShuleExamSubject extends BaseShuleModel
{
    protected $table = 'shule_exam_subjects';

    protected $fillable = [
        'exam_id',
        'subject_id',
        'max_marks',
        'pass_mark',
    ];

    protected static function booted(): void
    {
        static::saving(function (ShuleExamSubject $examSubject): void {
            $schoolId = $examSubject->school_id ?: app(SchoolContext::class)->id();
            if (!$schoolId) {
                throw new RuntimeException('No active school context for exam subject.');
            }

            $examSchoolId = DB::table('shule_exams')
                ->where('id', $examSubject->exam_id)
                ->value('school_id');
            if (!$examSchoolId || $examSchoolId !== $schoolId) {
                throw new RuntimeException('Exam subject must belong to active school.');
            }

            $subjectSchoolId = DB::table('shule_subjects')
                ->where('id', $examSubject->subject_id)
                ->value('school_id');
            if (!$subjectSchoolId || $subjectSchoolId !== $schoolId) {
                throw new RuntimeException('Exam subject must belong to active school.');
            }
        });
    }

    public function exam(): BelongsTo
    {
        return $this->belongsTo(ShuleExam::class, 'exam_id');
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(ShuleSubject::class, 'subject_id');
    }

    public function scores(): HasMany
    {
        return $this->hasMany(ShuleExamScore::class, 'exam_subject_id');
    }
}
