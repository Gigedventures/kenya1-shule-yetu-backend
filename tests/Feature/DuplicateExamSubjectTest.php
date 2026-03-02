<?php

namespace Tests\Feature;

use App\Modules\ShuleYetu\Exams\Services\ExamService;
use App\Modules\ShuleYetu\Models\ShuleAcademicYear;
use App\Modules\ShuleYetu\Models\ShuleClass;
use App\Modules\ShuleYetu\Models\ShuleExamType;
use App\Modules\ShuleYetu\Models\ShuleSchool;
use App\Modules\ShuleYetu\Models\ShuleSubject;
use App\Modules\ShuleYetu\Models\ShuleTerm;
use App\Modules\ShuleYetu\Support\Tenancy\SchoolContext;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

class DuplicateExamSubjectTest extends TestCase
{
    use RefreshDatabase;

    public function test_duplicate_exam_subject_is_blocked(): void
    {
        $school = ShuleSchool::query()->create([
            'name' => 'School D',
            'code' => 'EX-D-' . Str::upper(Str::random(5)),
            'status' => 'active',
        ]);

        $context = app(SchoolContext::class);
        $context->setId($school->id);

        $year = ShuleAcademicYear::query()->create([
            'name' => '2026',
            'start_date' => now()->startOfYear(),
            'end_date' => now()->endOfYear(),
            'is_active' => true,
        ]);
        $term = ShuleTerm::query()->create([
            'academic_year_id' => $year->id,
            'name' => 'Term 1',
            'start_date' => now()->startOfYear(),
            'end_date' => now()->startOfYear()->addMonths(3),
        ]);
        $class = ShuleClass::query()->create([
            'name' => 'Grade 1',
            'level' => 1,
        ]);
        $subject = ShuleSubject::query()->create([
            'name' => 'Chemistry',
            'code' => 'CHE',
            'is_core' => true,
        ]);
        DB::table('shule_class_subject')->insert([
            'id' => (string) Str::uuid(),
            'school_id' => $school->id,
            'class_id' => $class->id,
            'subject_id' => $subject->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $type = ShuleExamType::query()->create([
            'name' => 'Midterm',
            'weight' => 100,
            'is_active' => true,
        ]);

        $service = app(ExamService::class);
        $exam = $service->createExam([
            'academic_year_id' => $year->id,
            'term_id' => $term->id,
            'exam_type_id' => $type->id,
            'class_id' => $class->id,
            'title' => 'Midterm',
            'total_marks' => 100,
            'start_date' => now()->toDateString(),
        ]);

        $service->addSubjectToExam($exam->id, $subject->id, 100, 40);

        $this->expectException(QueryException::class);

        $service->addSubjectToExam($exam->id, $subject->id, 100, 40);
    }
}
