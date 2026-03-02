<?php

namespace Tests\Feature;

use App\Modules\ShuleYetu\Models\ShuleSchool;
use App\Modules\ShuleYetu\Models\ShuleStaff;
use App\Modules\ShuleYetu\Support\Tenancy\SchoolContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use RuntimeException;
use Tests\TestCase;

class StaffContextMissingTest extends TestCase
{
    use RefreshDatabase;

    public function test_staff_queries_fail_closed_without_context(): void
    {
        $school = ShuleSchool::query()->create([
            'name' => 'School Z',
            'code' => 'HR-Z-' . Str::upper(Str::random(5)),
            'status' => 'active',
        ]);

        $context = app(SchoolContext::class);
        $context->setId($school->id);

        ShuleStaff::query()->create([
            'first_name' => 'Zed',
            'last_name' => 'Zero',
            'staff_type' => 'teacher',
            'status' => 'active',
        ]);

        $context->clear();

        $this->assertSame(0, ShuleStaff::query()->count());
    }

    public function test_staff_create_requires_context(): void
    {
        $this->expectException(RuntimeException::class);

        ShuleStaff::query()->create([
            'first_name' => 'No',
            'last_name' => 'Context',
            'staff_type' => 'teacher',
            'status' => 'active',
        ]);
    }
}
