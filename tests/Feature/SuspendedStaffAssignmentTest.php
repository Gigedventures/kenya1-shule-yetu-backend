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

class SuspendedStaffAssignmentTest extends TestCase
{
    use RefreshDatabase;

    public function test_suspended_staff_cannot_be_assigned(): void
    {
        $school = ShuleSchool::query()->create([
            'name' => 'School S',
            'code' => 'HR-S-' . Str::upper(Str::random(5)),
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

        $teacher = ShuleStaff::query()->create([
            'first_name' => 'Suspended',
            'last_name' => 'Teacher',
            'staff_type' => 'teacher',
            'status' => 'suspended',
        ]);

        $this->expectException(RuntimeException::class);

        ShuleTeacherAssignment::query()->create([
            'academic_year_id' => $year->id,
            'term_id' => $term->id,
            'staff_id' => $teacher->id,
            'class_id' => $class->id,
            'is_class_teacher' => false,
        ]);
    }
}
