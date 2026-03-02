<?php

namespace Tests\Feature;

use App\Modules\ShuleYetu\Exams\Services\ExamService;
use App\Modules\ShuleYetu\Models\ShuleAcademicYear;
use App\Modules\ShuleYetu\Models\ShuleClass;
use App\Modules\ShuleYetu\Models\ShuleExamType;
use App\Modules\ShuleYetu\Models\ShuleSchool;
use App\Modules\ShuleYetu\Models\ShuleStudent;
use App\Modules\ShuleYetu\Models\ShuleSubject;
use App\Modules\ShuleYetu\Models\ShuleTerm;
use App\Modules\ShuleYetu\Support\Tenancy\SchoolContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;
use Tests\TestCase;

class CloseExamLockTest extends TestCase
{
    use RefreshDatabase;

    public function test_closed_exam_blocks_marks_entry(): void
    {
        $school = ShuleSchool::query()->create([
            'name' => 'School C',
            'code' => 'EX-C-' . Str::upper(Str::random(5)),
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
            'name' => 'Science',
            'code' => 'SCI',
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
            'name' => 'Endterm',
            'weight' => 100,
            'is_active' => true,
        ]);

        $student = ShuleStudent::query()->create([
            'first_name' => 'Closed',
            'last_name' => 'Case',
            'current_class_id' => $class->id,
            'status' => 'active',
        ]);

        $service = app(ExamService::class);
        $exam = $service->createExam([
            'academic_year_id' => $year->id,
            'term_id' => $term->id,
            'exam_type_id' => $type->id,
            'class_id' => $class->id,
            'title' => 'Endterm',
            'total_marks' => 100,
            'start_date' => now()->toDateString(),
        ]);

        $examSubject = $service->addSubjectToExam($exam->id, $subject->id, 100, 50);
        $service->publishExam($exam->id);
        $service->closeExam($exam->id);

        $this->expectException(RuntimeException::class);

        $service->enterMarksBulk($examSubject->id, [
            ['student_id' => $student->id, 'marks_obtained' => 90],
        ], $this->fakeUserWithPermission('exams.score'));
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
