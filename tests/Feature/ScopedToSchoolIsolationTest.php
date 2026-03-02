<?php

namespace Tests\Feature;

use App\Modules\ShuleYetu\Models\TenantScopeProbe;
use App\Modules\ShuleYetu\Support\Tenancy\SchoolContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Str;
use RuntimeException;
use Tests\TestCase;

class ScopedToSchoolIsolationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        if (!Schema::hasTable('shule_tenant_scope_probes')) {
            Schema::create('shule_tenant_scope_probes', function (Blueprint $table): void {
                $table->uuid('id')->primary();
                $table->uuid('school_id')->index();
                $table->string('name');
                $table->timestamps();
            });
        }
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('shule_tenant_scope_probes');
        parent::tearDown();
    }

    public function test_reads_fail_closed_without_context(): void
    {
        app(SchoolContext::class)->clear();

        DB::table('shule_tenant_scope_probes')->insert([
            'id' => (string) Str::uuid(),
            'school_id' => (string) Str::uuid(),
            'name' => 'Hidden Record',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->assertSame(0, TenantScopeProbe::query()->count());
    }

    public function test_writes_fail_closed_without_context(): void
    {
        app(SchoolContext::class)->clear();

        $this->expectException(RuntimeException::class);

        TenantScopeProbe::query()->create([
            'name' => 'Should Fail',
        ]);
    }
}

