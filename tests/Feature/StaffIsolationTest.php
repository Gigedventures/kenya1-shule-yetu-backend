<?php

namespace Tests\Feature;

use App\Modules\ShuleYetu\Models\ShuleSchool;
use App\Modules\ShuleYetu\Models\ShuleStaff;
use App\Modules\ShuleYetu\Support\Tenancy\SchoolContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class StaffIsolationTest extends TestCase
{
    use RefreshDatabase;

    public function test_staff_records_are_isolated_by_school(): void
    {
        $schoolA = ShuleSchool::query()->create([
            'name' => 'School A',
            'code' => 'HR-A-' . Str::upper(Str::random(5)),
            'status' => 'active',
        ]);
        $schoolB = ShuleSchool::query()->create([
            'name' => 'School B',
            'code' => 'HR-B-' . Str::upper(Str::random(5)),
            'status' => 'active',
        ]);

        $context = app(SchoolContext::class);
        $context->setId($schoolA->id);

        ShuleStaff::query()->create([
            'first_name' => 'Alice',
            'last_name' => 'A',
            'staff_type' => 'teacher',
            'status' => 'active',
        ]);

        $context->setId($schoolB->id);

        ShuleStaff::query()->create([
            'first_name' => 'Bob',
            'last_name' => 'B',
            'staff_type' => 'teacher',
            'status' => 'active',
        ]);

        $context->setId($schoolA->id);
        $this->assertSame(1, ShuleStaff::query()->count());

        $context->setId($schoolB->id);
        $this->assertSame(1, ShuleStaff::query()->count());
    }
}
