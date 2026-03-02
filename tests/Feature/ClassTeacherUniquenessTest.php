<?php

namespace Tests\Feature;

use App\Modules\ShuleYetu\Models\ShuleAcademicYear;
use App\Modules\ShuleYetu\Models\ShuleClass;
use App\Modules\ShuleYetu\Models\ShuleSchool;
use App\Modules\ShuleYetu\Models\ShuleStaff;
use App\Modules\ShuleYetu\Models\ShuleTeacherAssignment;
use App\Modules\ShuleYetu\Models\ShuleTerm;
use App\Modules\ShuleYetu\Support\Tenancy\SchoolContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use RuntimeException;
use Tests\TestCase;

class ClassTeacherUniquenessTest extends TestCase
{
    use RefreshDatabase;

    public function test_only_one_class_teacher_allowed_per_class_stream_term(): void
    {
        $school = ShuleSchool::query()->create([
            'name' => 'School C',
            'code' => 'HR-C-' . Str::upper(Str::random(5)),
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
            'name' => 'Grade 2',
            'level' => 2,
        ]);

        $teacherA = ShuleStaff::query()->create([
            'first_name' => 'Teacher',
            'last_name' => 'A',
            'staff_type' => 'teacher',
            'status' => 'active',
        ]);
        $teacherB = ShuleStaff::query()->create([
            'first_name' => 'Teacher',
            'last_name' => 'B',
            'staff_type' => 'teacher',
            'status' => 'active',
        ]);

        ShuleTeacherAssignment::query()->create([
            'academic_year_id' => $year->id,
            'term_id' => $term->id,
            'staff_id' => $teacherA->id,
            'class_id' => $class->id,
            'is_class_teacher' => true,
        ]);

        $this->expectException(RuntimeException::class);

        ShuleTeacherAssignment::query()->create([
            'academic_year_id' => $year->id,
            'term_id' => $term->id,
            'staff_id' => $teacherB->id,
            'class_id' => $class->id,
            'is_class_teacher' => true,
        ]);
    }
}
