<?php

namespace Tests\Feature;

use App\Models\User;
use App\Modules\ShuleYetu\Models\ShuleSchool;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Tests\TestCase;

class ShuleTenancyMiddlewareTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Route::middleware(['web', 'auth', 'shule.tenancy'])
            ->get('/_test/shule-tenancy', fn () => response()->json(['ok' => true]));

        Route::middleware(['api', 'auth', 'shule.tenancy'])
            ->get('/api/_test/shule-tenancy', fn () => response()->json(['ok' => true]));
    }

    public function test_missing_school_header_returns_403_for_api(): void
    {
        $user = User::factory()->create();
        $school = ShuleSchool::query()->create([
            'name' => 'Alpha School',
            'code' => 'ALPHA',
            'status' => 'active',
        ]);

        $this->attachMembership($school->id, $user->id, 'active');

        $this->actingAs($user)
            ->getJson('/api/_test/shule-tenancy')
            ->assertStatus(403);
    }

    public function test_suspended_school_returns_403(): void
    {
        $user = User::factory()->create();
        $school = ShuleSchool::query()->create([
            'name' => 'Suspended School',
            'code' => 'SUSP',
            'status' => 'suspended',
        ]);

        $this->attachMembership($school->id, $user->id, 'active');

        $this->actingAs($user)
            ->withHeader('X-School-Code', 'SUSP')
            ->getJson('/api/_test/shule-tenancy')
            ->assertStatus(403);
    }

    public function test_user_without_active_membership_returns_403(): void
    {
        $user = User::factory()->create();
        ShuleSchool::query()->create([
            'name' => 'No Membership School',
            'code' => 'NOMEMBER',
            'status' => 'active',
        ]);

        $this->actingAs($user)
            ->withHeader('X-School-Code', 'NOMEMBER')
            ->getJson('/api/_test/shule-tenancy')
            ->assertStatus(403);
    }

    private function attachMembership(string $schoolId, int $userId, string $status): void
    {
        DB::table('shule_school_user')->insert([
            'id' => (string) Str::uuid(),
            'school_id' => $schoolId,
            'user_id' => $userId,
            'status' => $status,
            'joined_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
