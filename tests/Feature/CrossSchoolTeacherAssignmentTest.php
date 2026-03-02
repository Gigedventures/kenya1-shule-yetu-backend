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

class CrossSchoolTeacherAssignmentTest extends TestCase
{
    use RefreshDatabase;

    public function test_teacher_assignment_rejects_cross_school_records(): void
    {
        $schoolA = ShuleSchool::query()->create([
            'name' => 'School A',
            'code' => 'HR-XA-' . Str::upper(Str::random(5)),
            'status' => 'active',
        ]);
        $schoolB = ShuleSchool::query()->create([
            'name' => 'School B',
            'code' => 'HR-XB-' . Str::upper(Str::random(5)),
            'status' => 'active',
        ]);

        $context = app(SchoolContext::class);
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
        $teacherA = ShuleStaff::query()->create([
            'first_name' => 'Teacher',
            'last_name' => 'A',
            'staff_type' => 'teacher',
            'status' => 'active',
        ]);

        $context->setId($schoolB->id);

        $yearB = ShuleAcademicYear::query()->create([
            'name' => '2026-B',
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

        $this->expectException(RuntimeException::class);

        ShuleTeacherAssignment::query()->create([
            'academic_year_id' => $yearB->id,
            'term_id' => $termB->id,
            'staff_id' => $teacherA->id,
            'class_id' => $classA->id,
            'is_class_teacher' => false,
        ]);
    }
}
