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

class CrossSchoolProtectionTest extends TestCase
{
    use RefreshDatabase;

    public function test_cross_school_marks_entry_is_blocked(): void
    {
        $schoolA = ShuleSchool::query()->create([
            'name' => 'School A',
            'code' => 'EX-XA-' . Str::upper(Str::random(5)),
            'status' => 'active',
        ]);
        $schoolB = ShuleSchool::query()->create([
            'name' => 'School B',
            'code' => 'EX-XB-' . Str::upper(Str::random(5)),
            'status' => 'active',
        ]);

        $context = app(SchoolContext::class);
        $service = app(ExamService::class);

        $context->setId($schoolA->id);
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
            'name' => 'Geography',
            'code' => 'GEO',
            'is_core' => true,
        ]);
        DB::table('shule_class_subject')->insert([
            'id' => (string) Str::uuid(),
            'school_id' => $schoolA->id,
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

        $exam = $service->createExam([
            'academic_year_id' => $year->id,
            'term_id' => $term->id,
            'exam_type_id' => $type->id,
            'class_id' => $class->id,
            'title' => 'CAT 1',
            'total_marks' => 100,
            'start_date' => now()->toDateString(),
        ]);
        $examSubject = $service->addSubjectToExam($exam->id, $subject->id, 100, 40);
        $service->publishExam($exam->id);

        $context->setId($schoolB->id);
        $classB = ShuleClass::query()->create([
            'name' => 'Grade 2',
            'level' => 2,
        ]);
        $student = ShuleStudent::query()->create([
            'first_name' => 'Cross',
            'last_name' => 'School',
            'current_class_id' => $classB->id,
            'status' => 'active',
        ]);

        $context->setId($schoolA->id);

        $this->expectException(RuntimeException::class);

        $service->enterMarksBulk($examSubject->id, [
            ['student_id' => $student->id, 'marks_obtained' => 50],
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
