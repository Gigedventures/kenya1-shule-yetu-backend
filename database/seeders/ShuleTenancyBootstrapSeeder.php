<?php

namespace Database\Seeders;

use App\Models\User;
use App\Modules\ShuleYetu\Models\ShuleSchool;
use App\Modules\ShuleYetu\Support\Tenancy\SchoolContext;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ShuleTenancyBootstrapSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::query()->first() ?? User::query()->create([
            'name' => 'Tenancy Admin',
            'email' => 'tenancy-admin@example.test',
            'password' => 'password',
        ]);

        $school = ShuleSchool::query()->first() ?? ShuleSchool::query()->create([
            'name' => 'Shule Yetu Demo School',
            'code' => 'DEMO-SCHOOL',
            'status' => 'active',
        ]);

        DB::table('shule_school_user')->updateOrInsert(
            ['school_id' => $school->id, 'user_id' => $user->id],
            [
                'id' => (string) Str::uuid(),
                'status' => 'active',
                'joined_at' => now(),
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );

        app(SchoolContext::class)->setId((string) $school->id);

        // When tenant models are introduced, create them after setting context:
        // app(SchoolContext::class)->setId((string) $school->id);
        // SomeTenantModel::create(['name' => 'Example']);
    }
}

