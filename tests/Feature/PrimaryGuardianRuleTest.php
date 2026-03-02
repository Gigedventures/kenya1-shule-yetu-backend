<?php

namespace Tests\Feature;

use App\Modules\ShuleYetu\Models\ShuleGuardian;
use App\Modules\ShuleYetu\Models\ShuleSchool;
use App\Modules\ShuleYetu\Models\ShuleStudent;
use App\Modules\ShuleYetu\Models\ShuleStudentGuardian;
use App\Modules\ShuleYetu\Support\Tenancy\SchoolContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class PrimaryGuardianRuleTest extends TestCase
{
    use RefreshDatabase;

    public function test_only_one_primary_guardian_per_student(): void
    {
        $school = ShuleSchool::query()->create([
            'name' => 'Guardian School',
            'code' => 'GUA-' . Str::upper(Str::random(5)),
            'status' => 'active',
        ]);

        app(SchoolContext::class)->setId($school->id);

        $student = ShuleStudent::query()->create([
            'admission_no' => 'ADM-100',
            'first_name' => 'Student',
            'last_name' => 'One',
            'status' => 'active',
        ]);

        $guardianA = ShuleGuardian::query()->create([
            'first_name' => 'Guardian',
            'last_name' => 'A',
        ]);
        $guardianB = ShuleGuardian::query()->create([
            'first_name' => 'Guardian',
            'last_name' => 'B',
        ]);

        ShuleStudentGuardian::query()->create([
            'student_id' => $student->id,
            'guardian_id' => $guardianA->id,
            'relationship' => 'guardian',
            'is_primary' => true,
        ]);

        ShuleStudentGuardian::query()->create([
            'student_id' => $student->id,
            'guardian_id' => $guardianB->id,
            'relationship' => 'guardian',
            'is_primary' => true,
        ]);

        $this->assertSame(1, ShuleStudentGuardian::query()->where('student_id', $student->id)->where('is_primary', true)->count());
        $this->assertTrue(ShuleStudentGuardian::query()->where('student_id', $student->id)->where('guardian_id', $guardianB->id)->value('is_primary'));
    }
}

