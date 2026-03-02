<?php

namespace App\Modules\ShuleYetu\Finance\Policies;

use App\Models\User;
use App\Modules\ShuleYetu\Models\ShuleFeeStructure;
use App\Modules\ShuleYetu\Support\Policies\ShuleBasePolicy;

class ShuleFeeStructurePolicy extends ShuleBasePolicy
{
    public function viewAny(User $user): bool
    {
        return $this->can($user, 'finance.view');
    }

    public function view(User $user, ShuleFeeStructure $structure): bool
    {
        return $this->can($user, 'finance.view');
    }

    public function create(User $user): bool
    {
        return $this->can($user, 'finance.manage');
    }

    public function update(User $user, ShuleFeeStructure $structure): bool
    {
        return $this->can($user, 'finance.manage');
    }

    public function delete(User $user, ShuleFeeStructure $structure): bool
    {
        return $this->can($user, 'finance.manage');
    }
}
