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
use App\Modules\ShuleYetu\Models\ShuleTermResult;
use App\Modules\ShuleYetu\Support\Tenancy\SchoolContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

class TermResultsAggregationTest extends TestCase
{
    use RefreshDatabase;

    public function test_term_results_aggregate_with_weights_and_rank(): void
    {
        $school = ShuleSchool::query()->create([
            'name' => 'School T',
            'code' => 'EX-T-' . Str::upper(Str::random(5)),
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
            'name' => 'Grade 3',
            'level' => 3,
        ]);
        $subject = ShuleSubject::query()->create([
            'name' => 'History',
            'code' => 'HIS',
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

        ShuleGradeBand::query()->create([
            'min_percentage' => 0,
            'max_percentage' => 100,
            'grade' => 'A',
        ]);

        $typeA = ShuleExamType::query()->create([
            'name' => 'Midterm',
            'weight' => 60,
            'is_active' => true,
        ]);
        $typeB = ShuleExamType::query()->create([
            'name' => 'Endterm',
            'weight' => 40,
            'is_active' => true,
        ]);

        $student1 = ShuleStudent::query()->create([
            'first_name' => 'Top',
            'last_name' => 'Student',
            'current_class_id' => $class->id,
            'status' => 'active',
        ]);
        $student2 = ShuleStudent::query()->create([
            'first_name' => 'Mid',
            'last_name' => 'Student',
            'current_class_id' => $class->id,
            'status' => 'active',
        ]);

        $service = app(ExamService::class);

        $examA = $service->createExam([
            'academic_year_id' => $year->id,
            'term_id' => $term->id,
            'exam_type_id' => $typeA->id,
            'class_id' => $class->id,
            'title' => 'Midterm',
            'total_marks' => 100,
            'start_date' => now()->toDateString(),
        ]);
        $subjectA = $service->addSubjectToExam($examA->id, $subject->id, 100, 40);
        $service->publishExam($examA->id);

        $examB = $service->createExam([
            'academic_year_id' => $year->id,
            'term_id' => $term->id,
            'exam_type_id' => $typeB->id,
            'class_id' => $class->id,
            'title' => 'Endterm',
            'total_marks' => 100,
            'start_date' => now()->toDateString(),
        ]);
        $subjectB = $service->addSubjectToExam($examB->id, $subject->id, 100, 40);
        $service->publishExam($examB->id);

        $actor = $this->fakeUserWithPermission('exams.score');

        $service->enterMarksBulk($subjectA->id, [
            ['student_id' => $student1->id, 'marks_obtained' => 70],
            ['student_id' => $student2->id, 'marks_obtained' => 60],
        ], $actor);
        $service->enterMarksBulk($subjectB->id, [
            ['student_id' => $student1->id, 'marks_obtained' => 90],
            ['student_id' => $student2->id, 'marks_obtained' => 50],
        ], $actor);

        $service->closeExam($examA->id);
        $service->closeExam($examB->id);

        $service->calculateTermResults($term->id, ['ranking_enabled' => true]);

        $result1 = ShuleTermResult::query()->where('student_id', $student1->id)->firstOrFail();
        $result2 = ShuleTermResult::query()->where('student_id', $student2->id)->firstOrFail();

        $this->assertSame('78.00', $result1->total_percentage);
        $this->assertSame(1, $result1->rank);
        $this->assertSame(2, $result2->rank);
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
