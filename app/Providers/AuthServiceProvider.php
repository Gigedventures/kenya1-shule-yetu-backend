<?php

namespace App\Providers;

// use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        \App\Modules\ShuleYetu\Models\ShuleDepartment::class => \App\Modules\ShuleYetu\HR\Policies\ShuleDepartmentPolicy::class,
        \App\Modules\ShuleYetu\Models\ShuleStaff::class => \App\Modules\ShuleYetu\HR\Policies\ShuleStaffPolicy::class,
        \App\Modules\ShuleYetu\Models\ShuleTeacherAssignment::class => \App\Modules\ShuleYetu\HR\Policies\ShuleTeacherAssignmentPolicy::class,
        \App\Modules\ShuleYetu\Models\ShuleStaffAttendance::class => \App\Modules\ShuleYetu\HR\Policies\ShuleStaffAttendancePolicy::class,
        \App\Modules\ShuleYetu\Models\ShuleFeeStructure::class => \App\Modules\ShuleYetu\Finance\Policies\ShuleFeeStructurePolicy::class,
        \App\Modules\ShuleYetu\Models\ShuleStudentBill::class => \App\Modules\ShuleYetu\Finance\Policies\ShuleStudentBillPolicy::class,
        \App\Modules\ShuleYetu\Models\ShulePayment::class => \App\Modules\ShuleYetu\Finance\Policies\ShulePaymentPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        //
    }
}
