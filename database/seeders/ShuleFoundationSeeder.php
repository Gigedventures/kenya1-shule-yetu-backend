<?php

namespace Database\Seeders;

use App\Models\User;
use App\Modules\ShuleYetu\Models\School;
use App\Modules\ShuleYetu\Models\ShuleClass;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ShuleFoundationSeeder extends Seeder
{
    public function run(): void
    {
        // Ensure an owner user exists (we'll use your Super Admin if present)
        $owner = User::where('email', 'admin@shuleyetu.test')->first();

        if (!$owner) {
            $owner = User::updateOrCreate(
                ['email' => 'admin@shuleyetu.test'],
                ['name' => 'Super Admin', 'password' => bcrypt('Admin@12345')]
            );
        }

        // Create or update school with required fields
        $school = School::updateOrCreate(
            ['code' => 'DPS001'],
            [
                'uuid' => (string) Str::uuid(),
                'owner_user_id' => $owner->id,
                'name' => 'Demo Primary School',
                'phone' => '0700000000',
                'email' => 'info@demo.test',
                'location' => 'Nairobi',
                'currency' => 'KES',
                'country' => 'KE',
                'status' => 'active',
                'activated_at' => now(),
            ]
        );

        // Seed classes (linked to the school)
        $classes = ['Grade 1', 'Grade 2', 'Grade 3', 'Grade 4', 'Grade 5', 'Grade 6'];

        foreach ($classes as $className) {
            ShuleClass::updateOrCreate(
                ['school_id' => $school->id, 'name' => $className],
                ['uuid' => (string) Str::uuid()]
            );
        }
    }
}
