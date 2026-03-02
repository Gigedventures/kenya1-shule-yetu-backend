<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ModulesSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('modules')->truncate();

        $modules = [

            // 🔥 LIVE MODULES (PHASE 1)
            [
                'name' => 'Shule Yetu',
                'slug' => 'shule-yetu',
                'is_active' => 1,
                'description' => 'Complete school management system for students, teachers and parents.',
                'coming_soon_message' => null,
                'image' => null,
                'display_order' => 1,
            ],

            [
                'name' => 'Kenya Cademy',
                'slug' => 'kenya-cademy',
                'is_active' => 1,
                'description' => 'Digital learning platform for courses, skills and certifications.',
                'coming_soon_message' => null,
                'image' => null,
                'display_order' => 2,
            ],

            [
                'name' => 'My Chamaa',
                'slug' => 'my-chamaa',
                'is_active' => 1,
                'description' => 'Smart group savings, loans and financial collaboration.',
                'coming_soon_message' => null,
                'image' => null,
                'display_order' => 3,
            ],

            // 🚧 COMING SOON MODULES
            [
                'name' => 'Twende',
                'slug' => 'twende',
                'is_active' => 0,
                'description' => 'Ride hailing and logistics platform.',
                'coming_soon_message' => 'Launching Soon on Kenya 1',
                'image' => null,
                'display_order' => 4,
            ],

            [
                'name' => 'E-Pharmacy',
                'slug' => 'e-pharmacy',
                'is_active' => 0,
                'description' => 'Order medicine safely from licensed pharmacies.',
                'coming_soon_message' => 'Launching Soon on Kenya 1',
                'image' => null,
                'display_order' => 5,
            ],

            [
                'name' => 'Gas Monitor',
                'slug' => 'gas-monitor',
                'is_active' => 0,
                'description' => 'AI-powered gas tracking and safety monitoring.',
                'coming_soon_message' => 'Launching Soon on Kenya 1',
                'image' => null,
                'display_order' => 6,
            ],

            [
                'name' => 'Harambee',
                'slug' => 'harambee',
                'is_active' => 0,
                'description' => 'Crowd fundraising for community causes.',
                'coming_soon_message' => 'Launching Soon on Kenya 1',
                'image' => null,
                'display_order' => 7,
            ],

            [
                'name' => 'My Crib',
                'slug' => 'my-crib',
                'is_active' => 0,
                'description' => 'Property and estate management platform.',
                'coming_soon_message' => 'Launching Soon on Kenya 1',
                'image' => null,
                'display_order' => 8,
            ],

            [
                'name' => 'Ticketing',
                'slug' => 'ticketing',
                'is_active' => 0,
                'description' => 'Event ticketing and access control.',
                'coming_soon_message' => 'Launching Soon on Kenya 1',
                'image' => null,
                'display_order' => 9,
            ],

            [
                'name' => 'Loto Play',
                'slug' => 'loto-play',
                'is_active' => 0,
                'description' => 'Digital lottery and jackpot gaming.',
                'coming_soon_message' => 'Launching Soon on Kenya 1',
                'image' => null,
                'display_order' => 10,
            ],
        ];

        DB::table('modules')->insert($modules);
    }
}
