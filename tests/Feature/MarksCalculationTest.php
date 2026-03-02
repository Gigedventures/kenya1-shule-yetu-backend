<?php

namespace Tests\Feature;

use App\Modules\ShuleYetu\Exams\Services\ExamService;
use App\Modules\ShuleYetu\Models\ShuleAcademicYear;
use App\Modules\ShuleYetu\Models\ShuleClass;
use App\Modules\ShuleYetu\Models\ShuleExamScore;
use App\Modules\ShuleYetu\Models\ShuleExamType;
use App\Modules\ShuleYetu\Models\ShuleGradeBand;
use App\Modules\ShuleYetu\Models\ShuleSchool;
use App\Modules\ShuleYetu\Models\ShuleStudent;
use App\Modules\ShuleYetu\Models\ShuleSubject;
use App\Modules\ShuleYetu\Models\ShuleTerm;
use App\Modules\ShuleYetu\Support\Tenancy\SchoolContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class MarksCalculationTest extends TestCase
{
    use RefreshDatabase;

    public function test_percentage_and_grade_are_calculated(): void
    {
        $school = ShuleSchool::query()->create([
            'name' => 'School M',
            'code' => 'EX-M-' . Str::upper(Str::random(5)),
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
            'name' => 'Math',
            'code' => 'MTH',
            'is_core' => true,
        ]);
        \Illuminate\Support\Facades\DB::table('shule_class_subject')->insert([
            'id' => (string) \Illuminate\Support\Str::uuid(),
            'school_id' => $school->id,
            'class_id' => $class->id,
            'subject_id' => $subject->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $type = ShuleExamType::query()->create([
            'name' => 'CAT',
            'weight' => 100,
            'is_active' => true,
        ]);

        ShuleGradeBand::query()->create([
            'min_percentage' => 80,
            'max_percentage' => 100,
            'grade' => 'A',
        ]);
        ShuleGradeBand::query()->create([
            'min_percentage' => 0,
            'max_percentage' => 79.99,
            'grade' => 'B',
        ]);

        $student = ShuleStudent::query()->create([
            'first_name' => 'Mary',
            'last_name' => 'M',
            'current_class_id' => $class->id,
            'status' => 'active',
        ]);

        $service = app(ExamService::class);
        $exam = $service->createExam([
            'academic_year_id' => $year->id,
            'term_id' => $term->id,
            'exam_type_id' => $type->id,
            'class_id' => $class->id,
            'title' => 'CAT 1',
            'total_marks' => 100,
            'start_date' => now()->toDateString(),
        ]);

        $examSubject = $service->addSubjectToExam($exam->id, $subject->id, 100, 50);
        $service->publishExam($exam->id);

        $service->enterMarksBulk($examSubject->id, [
            ['student_id' => $student->id, 'marks_obtained' => 80],
        ], $this->fakeUserWithPermission('exams.score'));

        $score = ShuleExamScore::query()->firstOrFail();
        $this->assertSame('80.00', $score->percentage);
        $this->assertSame('A', $score->grade);
    }

    private function fakeUserWithPermission(string $permission): \App\Models\User
    {
        $user = \App\Models\User::query()->create([
            'name' => 'Tester',
            'email' => 'tester+' . Str::random(5) . '@example.com',
            'password' => bcrypt('secret'),
            'is_system_admin' => false,
        ]);

        $user->givePermissionTo($permission);

        return $user;
    }
}
