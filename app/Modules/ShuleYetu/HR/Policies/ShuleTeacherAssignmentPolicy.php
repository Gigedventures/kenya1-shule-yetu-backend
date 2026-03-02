<?php

namespace App\Modules\ShuleYetu\HR\Policies;

use App\Models\User;
use App\Modules\ShuleYetu\Models\ShuleTeacherAssignment;
use App\Modules\ShuleYetu\Support\Policies\ShuleBasePolicy;

class ShuleTeacherAssignmentPolicy extends ShuleBasePolicy
{
    public function viewAny(User $user): bool
    {
        return $this->can($user, 'teacher_assignments.manage');
    }

    public function view(User $user, ShuleTeacherAssignment $assignment): bool
    {
        return $this->can($user, 'teacher_assignments.manage');
    }

    public function create(User $user): bool
    {
        return $this->can($user, 'teacher_assignments.manage');
    }

    public function update(User $user, ShuleTeacherAssignment $assignment): bool
    {
        return $this->can($user, 'teacher_assignments.manage');
    }

    public function delete(User $user, ShuleTeacherAssignment $assignment): bool
    {
        return $this->can($user, 'teacher_assignments.manage');
    }
}
