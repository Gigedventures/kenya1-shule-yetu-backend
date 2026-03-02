<?php

namespace Tests\Feature;

use App\Models\User;
use App\Modules\ShuleYetu\Models\ShuleSchool;
use App\Modules\ShuleYetu\Support\Rbac\ShuleRbacService;
use App\Modules\ShuleYetu\Support\Tenancy\SchoolContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Tests\TestCase;

class SchoolSwitchPermissionContextTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Route::middleware(['web', 'auth', 'shule.tenancy'])
            ->get('/_test/permission-check', function () {
                /** @var \App\Models\User $user */
                $user = auth()->user();

                return response()->json([
                    'allowed' => $user->hasPermission('manage_students'),
                ]);
            });
    }

    public function test_switching_schools_changes_permission_resolution(): void
    {
        $user = User::factory()->create();

        $schoolA = ShuleSchool::query()->create([
            'name' => 'School A',
            'code' => 'SWITCH-A',
            'status' => 'active',
        ]);
        $schoolB = ShuleSchool::query()->create([
            'name' => 'School B',
            'code' => 'SWITCH-B',
            'status' => 'active',
        ]);

        foreach ([$schoolA, $schoolB] as $school) {
            DB::table('shule_school_user')->insert([
                'id' => (string) Str::uuid(),
                'school_id' => $school->id,
                'user_id' => $user->id,
                'status' => 'active',
                'joined_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $context = app(SchoolContext::class);
        $rbac = app(ShuleRbacService::class);
        $context->setId($schoolA->id);
        $rbac->assignRole($user, 'Principal');
        $rbac->givePermissionToRole('Principal', 'manage_students');

        $this->actingAs($user)
            ->post(route('shule.schools.switch', ['code' => 'SWITCH-A']))
            ->assertRedirect('/admin');

        $this->actingAs($user)
            ->get('/_test/permission-check')
            ->assertOk()
            ->assertJson(['allowed' => true]);

        $this->actingAs($user)
            ->post(route('shule.schools.switch', ['code' => 'SWITCH-B']))
            ->assertRedirect('/admin');

        $this->actingAs($user)
            ->get('/_test/permission-check')
            ->assertOk()
            ->assertJson(['allowed' => false]);
    }
}

