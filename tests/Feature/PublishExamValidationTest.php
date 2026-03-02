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
use RuntimeException;
use Tests\TestCase;

class PublishExamValidationTest extends TestCase
{
    use RefreshDatabase;

    public function test_exam_cannot_publish_without_subjects(): void
    {
        $school = ShuleSchool::query()->create([
            'name' => 'School P',
            'code' => 'EX-P-' . Str::upper(Str::random(5)),
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
            'title' => 'Midterm 1',
            'total_marks' => 100,
            'start_date' => now()->toDateString(),
        ]);

        $this->expectException(RuntimeException::class);

        $service->publishExam($exam->id);
    }
}
