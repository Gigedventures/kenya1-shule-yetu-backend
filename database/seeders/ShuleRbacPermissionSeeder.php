<?php

namespace Database\Seeders;

use App\Modules\ShuleYetu\Models\ShulePermission;
use Illuminate\Database\Seeder;

class ShuleRbacPermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            'academic_years.view',
            'academic_years.create',
            'academic_years.manage',
            'terms.manage',
            'classes.manage',
            'subjects.manage',
            'students.view',
            'students.manage',
            'guardians.manage',
            'enrollments.manage',
            'staff.view',
            'staff.manage',
            'teacher_assignments.manage',
            'staff_attendance.manage',
            'exams.view',
            'exams.manage',
            'exams.publish',
            'exams.score',
            'exams.results.calculate',
            'finance.view',
            'finance.manage',
            'finance.payments.record',
            'finance.reports.view',
        ];

        foreach ($permissions as $permission) {
            ShulePermission::query()->firstOrCreate(
                ['name' => $permission],
                ['guard_name' => 'web']
            );
        }
    }
}
