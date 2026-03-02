<?php

namespace Tests\Feature;

use App\Models\User;
use App\Modules\ShuleYetu\Models\ShuleSchool;
use App\Modules\ShuleYetu\Support\Rbac\ShuleRbacService;
use App\Modules\ShuleYetu\Support\Tenancy\SchoolContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class RoleIsolationTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_role_is_isolated_per_school_context(): void
    {
        $schoolA = ShuleSchool::query()->create([
            'name' => 'School A',
            'code' => 'SCHOOL-A-' . Str::upper(Str::random(6)),
            'status' => 'active',
        ]);
        $schoolB = ShuleSchool::query()->create([
            'name' => 'School B',
            'code' => 'SCHOOL-B-' . Str::upper(Str::random(6)),
            'status' => 'active',
        ]);
        $user = User::factory()->create();

        $context = app(SchoolContext::class);
        $rbac = app(ShuleRbacService::class);

        $context->setId($schoolA->id);
        $rbac->assignRole($user, 'Principal');

        $context->setId($schoolA->id);
        $this->assertTrue($rbac->userHasRole($user, 'Principal'));

        $context->setId($schoolB->id);
        $this->assertFalse($rbac->userHasRole($user, 'Principal'));
    }
}
