<?php

namespace App\Modules\ShuleYetu\Finance\Policies;

use App\Models\User;
use App\Modules\ShuleYetu\Models\ShuleStudentBill;
use App\Modules\ShuleYetu\Support\Policies\ShuleBasePolicy;

class ShuleStudentBillPolicy extends ShuleBasePolicy
{
    public function viewAny(User $user): bool
    {
        return $this->can($user, 'finance.view');
    }

    public function view(User $user, ShuleStudentBill $bill): bool
    {
        return $this->can($user, 'finance.view');
    }
}
