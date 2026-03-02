<?php

namespace Tests\Feature;

use App\Modules\ShuleYetu\Exams\Services\ExamService;
use App\Modules\ShuleYetu\Models\ShuleAcademicYear;
use App\Modules\ShuleYetu\Models\ShuleClass;
use App\Modules\ShuleYetu\Models\ShuleExamType;
use App\Modules\ShuleYetu\Models\ShuleSchool;
use App\Modules\ShuleYetu\Models\ShuleTerm;
use App\Modules\ShuleYetu\Support\Tenancy\SchoolContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class ExamIsolationTest extends TestCase
{
    use RefreshDatabase;

    public function test_exam_records_are_isolated_by_school(): void
    {
        $schoolA = ShuleSchool::query()->create([
            'name' => 'School A',
            'code' => 'EX-A-' . Str::upper(Str::random(5)),
            'status' => 'active',
        ]);
        $schoolB = ShuleSchool::query()->create([
            'name' => 'School B',
            'code' => 'EX-B-' . Str::upper(Str::random(5)),
            'status' => 'active',
        ]);

        $context = app(SchoolContext::class);
        $service = app(ExamService::class);

        $context->setId($schoolA->id);
        $yearA = ShuleAcademicYear::query()->create([
            'name' => '2026',
            'start_date' => now()->startOfYear(),
            'end_date' => now()->endOfYear(),
            'is_active' => true,
        ]);
        $termA = ShuleTerm::query()->create([
            'academic_year_id' => $yearA->id,
            'name' => 'Term 1',
            'start_date' => now()->startOfYear(),
            'end_date' => now()->startOfYear()->addMonths(3),
        ]);
        $classA = ShuleClass::query()->create([
            'name' => 'Grade 1',
            'level' => 1,
        ]);
        $typeA = ShuleExamType::query()->create([
            'name' => 'Midterm',
            'weight' => 100,
            'is_active' => true,
        ]);

        $service->createExam([
            'academic_year_id' => $yearA->id,
            'term_id' => $termA->id,
            'exam_type_id' => $typeA->id,
            'class_id' => $classA->id,
            'title' => 'Midterm 1',
            'total_marks' => 100,
            'start_date' => now()->toDateString(),
        ]);

        $context->setId($schoolB->id);
        $yearB = ShuleAcademicYear::query()->create([
            'name' => '2026B',
            'start_date' => now()->startOfYear(),
            'end_date' => now()->endOfYear(),
            'is_active' => true,
        ]);
        $termB = ShuleTerm::query()->create([
            'academic_year_id' => $yearB->id,
            'name' => 'Term 1',
            'start_date' => now()->startOfYear(),
            'end_date' => now()->startOfYear()->addMonths(3),
        ]);
        $classB = ShuleClass::query()->create([
            'name' => 'Grade 2',
            'level' => 2,
        ]);
        $typeB = ShuleExamType::query()->create([
            'name' => 'Midterm',
            'weight' => 100,
            'is_active' => true,
        ]);

        $service->createExam([
            'academic_year_id' => $yearB->id,
            'term_id' => $termB->id,
            'exam_type_id' => $typeB->id,
            'class_id' => $classB->id,
            'title' => 'Midterm 1',
            'total_marks' => 100,
            'start_date' => now()->toDateString(),
        ]);

        $context->setId($schoolA->id);
        $this->assertSame(1, \App\Modules\ShuleYetu\Models\ShuleExam::query()->count());

        $context->setId($schoolB->id);
        $this->assertSame(1, \App\Modules\ShuleYetu\Models\ShuleExam::query()->count());
    }
}
