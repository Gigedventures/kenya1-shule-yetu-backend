<?php

namespace Tests\Feature;

use App\Modules\ShuleYetu\Models\ShuleClass;
use App\Modules\ShuleYetu\Models\ShuleSchool;
use App\Modules\ShuleYetu\Models\ShuleStream;
use App\Modules\ShuleYetu\Models\ShuleStudent;
use App\Modules\ShuleYetu\Support\Tenancy\SchoolContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use RuntimeException;
use Tests\TestCase;

class CrossSchoolStudentAssignmentTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_cannot_be_assigned_class_from_other_school(): void
    {
        $schoolA = ShuleSchool::query()->create([
            'name' => 'School A',
            'code' => 'CSA-A-' . Str::upper(Str::random(5)),
            'status' => 'active',
        ]);
        $schoolB = ShuleSchool::query()->create([
            'name' => 'School B',
            'code' => 'CSA-B-' . Str::upper(Str::random(5)),
            'status' => 'active',
        ]);

        $context = app(SchoolContext::class);

        $context->setId($schoolA->id);
        $classA = ShuleClass::query()->create([
            'name' => 'Grade 4',
            'level' => 4,
        ]);
        ShuleStream::query()->create([
            'class_id' => $classA->id,
            'name' => 'Blue',
            'capacity' => 30,
        ]);

        $context->setId($schoolB->id);
        ShuleClass::query()->create([
            'name' => 'Grade 5',
            'level' => 5,
        ]);

        $this->expectException(RuntimeException::class);

        ShuleStudent::query()->create([
            'admission_no' => 'ADM-777',
            'first_name' => 'Cross',
            'last_name' => 'School',
            'status' => 'active',
            'current_class_id' => $classA->id,
        ]);
    }
}

