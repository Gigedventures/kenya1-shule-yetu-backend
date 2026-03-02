<?php

namespace Tests\Feature;

use App\Models\User;
use App\Modules\ShuleYetu\Models\ShuleSchool;
use App\Modules\ShuleYetu\Support\Rbac\ShuleRbacService;
use App\Modules\ShuleYetu\Support\Tenancy\SchoolContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use RuntimeException;
use Tests\TestCase;

class SuperAdminTest extends TestCase
{
    use RefreshDatabase;

    public function test_system_admin_has_all_permissions_inside_context(): void
    {
        $school = ShuleSchool::query()->create([
            'name' => 'Admin School',
            'code' => 'ADMIN-SCHOOL',
            'status' => 'active',
        ]);
        $user = User::factory()->create([
            'is_system_admin' => true,
        ]);

        app(SchoolContext::class)->setId($school->id);

        $this->assertTrue(app(ShuleRbacService::class)->userHasPermission($user, 'anything'));
    }

    public function test_system_admin_still_requires_context(): void
    {
        $user = User::factory()->create([
            'is_system_admin' => true,
        ]);

        app(SchoolContext::class)->clear();

        $this->expectException(RuntimeException::class);

        app(ShuleRbacService::class)->userHasPermission($user, 'anything');
    }
}

