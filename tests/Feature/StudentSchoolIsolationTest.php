<?php

namespace Tests\Feature;

use App\Modules\ShuleYetu\Models\ShuleSchool;
use App\Modules\ShuleYetu\Models\ShuleStudent;
use App\Modules\ShuleYetu\Support\Tenancy\SchoolContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class StudentSchoolIsolationTest extends TestCase
{
    use RefreshDatabase;

    public function test_students_are_isolated_by_school_context(): void
    {
        $schoolA = ShuleSchool::query()->create([
            'name' => 'School A',
            'code' => 'STU-A-' . Str::upper(Str::random(5)),
            'status' => 'active',
        ]);
        $schoolB = ShuleSchool::query()->create([
            'name' => 'School B',
            'code' => 'STU-B-' . Str::upper(Str::random(5)),
            'status' => 'active',
        ]);

        $context = app(SchoolContext::class);
        $context->setId($schoolA->id);

        ShuleStudent::query()->create([
            'admission_no' => 'ADM-001',
            'first_name' => 'Jane',
            'last_name' => 'Doe',
            'status' => 'active',
        ]);

        $context->setId($schoolB->id);

        $this->assertSame(0, ShuleStudent::query()->count());
    }
}

