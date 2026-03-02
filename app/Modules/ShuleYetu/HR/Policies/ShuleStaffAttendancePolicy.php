<?php

namespace App\Modules\ShuleYetu\HR\Policies;

use App\Models\User;
use App\Modules\ShuleYetu\Models\ShuleStaffAttendance;
use App\Modules\ShuleYetu\Support\Policies\ShuleBasePolicy;

class ShuleStaffAttendancePolicy extends ShuleBasePolicy
{
    public function viewAny(User $user): bool
    {
        return $this->can($user, 'staff_attendance.manage');
    }

    public function view(User $user, ShuleStaffAttendance $attendance): bool
    {
        return $this->can($user, 'staff_attendance.manage');
    }

    public function create(User $user): bool
    {
        return $this->can($user, 'staff_attendance.manage');
    }

    public function update(User $user, ShuleStaffAttendance $attendance): bool
    {
        return $this->can($user, 'staff_attendance.manage');
    }

    public function delete(User $user, ShuleStaffAttendance $attendance): bool
    {
        return $this->can($user, 'staff_attendance.manage');
    }
}
