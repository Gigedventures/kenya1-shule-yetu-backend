<?php

namespace App\Modules\ShuleYetu\Support\Policies;

use App\Models\User;

abstract class ShuleBasePolicy
{
    protected function can(User $user, string $permission): bool
    {
        return $user->hasPermission($permission);
    }
}

