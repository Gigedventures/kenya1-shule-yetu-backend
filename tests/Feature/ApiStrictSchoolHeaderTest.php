<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class ApiStrictSchoolHeaderTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Route::middleware(['api', 'auth', 'shule.tenancy'])
            ->get('/api/_test/shule-strict-header', fn () => response()->json(['ok' => true]));
    }

    public function test_api_route_without_school_header_returns_403(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->getJson('/api/_test/shule-strict-header')
            ->assertStatus(403);
    }
}

