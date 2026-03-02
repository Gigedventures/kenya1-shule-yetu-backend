<?php

namespace Database\Factories;

use App\Modules\ShuleYetu\Models\ShuleSchool;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<ShuleSchool>
 */
class ShuleSchoolFactory extends Factory
{
    protected $model = ShuleSchool::class;

    public function definition(): array
    {
        return [
            'name' => 'School ' . Str::upper(Str::random(4)),
            'code' => 'SCH-' . Str::upper(Str::random(6)),
            'status' => 'active',
        ];
    }
}
