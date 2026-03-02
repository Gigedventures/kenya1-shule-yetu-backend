<?php

namespace App\Modules\ShuleYetu\HR\Policies;

use App\Models\User;
use App\Modules\ShuleYetu\Models\ShuleDepartment;
use App\Modules\ShuleYetu\Support\Policies\ShuleBasePolicy;

class ShuleDepartmentPolicy extends ShuleBasePolicy
{
    public function viewAny(User $user): bool
    {
        return $this->can($user, 'staff.view');
    }

    public function view(User $user, ShuleDepartment $department): bool
    {
        return $this->can($user, 'staff.view');
    }

    public function create(User $user): bool
    {
        return $this->can($user, 'staff.manage');
    }

    public function update(User $user, ShuleDepartment $department): bool
    {
        return $this->can($user, 'staff.manage');
    }

    public function delete(User $user, ShuleDepartment $department): bool
    {
        return $this->can($user, 'staff.manage');
    }
}
