<?php

namespace Database\Factories;

use App\Modules\ShuleYetu\Models\ShuleSchool;
use App\Modules\ShuleYetu\Models\ShuleStudentBill;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * @extends Factory<ShuleStudentBill>
 */
class ShuleStudentBillFactory extends Factory
{
    protected $model = ShuleStudentBill::class;

    public function definition(): array
    {
        return [
            'school_id' => ShuleSchool::factory(),
            'student_id' => function (array $attributes): string {
                return $this->createStudentForSchool((string) $attributes['school_id']);
            },
            'fee_structure_id' => function (array $attributes): string {
                return $this->createFeeStructureForSchool((string) $attributes['school_id']);
            },
            'total_amount' => 1000,
            'paid_amount' => 0,
            'balance' => 1000,
            'status' => 'unpaid',
        ];
    }

    private function createStudentForSchool(string $schoolId): string
    {
        $now = now();
        $classId = (string) Str::uuid();
        DB::table('shule_classes')->insert([
            'id' => $classId,
            'school_id' => $schoolId,
            'name' => 'Class ' . Str::upper(Str::random(5)),
            'level' => $this->faker->numberBetween(1, 8),
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $studentId = (string) Str::uuid();
        DB::table('shule_students')->insert([
            'id' => $studentId,
            'school_id' => $schoolId,
            'admission_no' => 'ADM-' . Str::upper(Str::random(8)),
            'first_name' => $this->faker->firstName(),
            'last_name' => $this->faker->lastName(),
            'status' => 'active',
            'current_class_id' => $classId,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        return $studentId;
    }

    private function createFeeStructureForSchool(string $schoolId): string
    {
        $now = now();
        $academicYearId = (string) Str::uuid();
        $classId = (string) Str::uuid();
        $termId = (string) Str::uuid();

        DB::table('shule_academic_years')->insert([
            'id' => $academicYearId,
            'school_id' => $schoolId,
            'name' => 'AY-' . Str::upper(Str::random(4)),
            'start_date' => now()->startOfYear()->toDateString(),
            'end_date' => now()->endOfYear()->toDateString(),
            'is_active' => true,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        DB::table('shule_classes')->insert([
            'id' => $classId,
            'school_id' => $schoolId,
            'name' => 'Class ' . Str::upper(Str::random(6)),
            'level' => $this->faker->numberBetween(1, 8),
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        DB::table('shule_terms')->insert([
            'id' => $termId,
            'school_id' => $schoolId,
            'academic_year_id' => $academicYearId,
            'name' => 'Term ' . $this->faker->numberBetween(1, 3) . '-' . Str::upper(Str::random(3)),
            'start_date' => now()->startOfYear()->toDateString(),
            'end_date' => now()->startOfYear()->addMonths(3)->toDateString(),
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $structureId = (string) Str::uuid();
        DB::table('shule_fee_structures')->insert([
            'id' => $structureId,
            'school_id' => $schoolId,
            'academic_year_id' => $academicYearId,
            'term_id' => $termId,
            'class_id' => $classId,
            'name' => 'Fees ' . Str::upper(Str::random(5)),
            'is_active' => true,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        return $structureId;
    }
}
