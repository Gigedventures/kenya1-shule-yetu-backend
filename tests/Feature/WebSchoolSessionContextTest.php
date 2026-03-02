<?php

namespace Tests\Feature;

use App\Models\User;
use App\Modules\ShuleYetu\Models\ShuleSchool;
use App\Modules\ShuleYetu\Support\Tenancy\SchoolContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Tests\TestCase;

class WebSchoolSessionContextTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Route::middleware(['web', 'auth', 'shule.tenancy'])
            ->get('/_test/web-school-context', function () {
                return response()->json([
                    'school_id' => app(SchoolContext::class)->id(),
                ]);
            });
    }

    public function test_switching_school_sets_session_and_resolves_context(): void
    {
        $user = User::factory()->create();
        $school = ShuleSchool::query()->create([
            'name' => 'Session School',
            'code' => 'SESSION-SCHOOL',
            'status' => 'active',
        ]);

        DB::table('shule_school_user')->insert([
            'id' => (string) Str::uuid(),
            'school_id' => $school->id,
            'user_id' => $user->id,
            'status' => 'active',
            'joined_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->actingAs($user)
            ->post(route('shule.schools.switch', ['code' => 'SESSION-SCHOOL']))
            ->assertRedirect('/admin')
            ->assertSessionHas('active_school_id', $school->id);

        $this->actingAs($user)
            ->get('/_test/web-school-context')
            ->assertOk()
            ->assertJson(['school_id' => $school->id]);
    }
}

