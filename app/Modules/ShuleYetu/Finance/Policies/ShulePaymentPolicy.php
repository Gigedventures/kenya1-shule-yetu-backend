<?php

namespace App\Modules\ShuleYetu\Finance\Policies;

use App\Models\User;
use App\Modules\ShuleYetu\Models\ShulePayment;
use App\Modules\ShuleYetu\Support\Policies\ShuleBasePolicy;

class ShulePaymentPolicy extends ShuleBasePolicy
{
    public function viewAny(User $user): bool
    {
        return $this->can($user, 'finance.view');
    }

    public function view(User $user, ShulePayment $payment): bool
    {
        return $this->can($user, 'finance.view');
    }

    public function create(User $user): bool
    {
        return $this->can($user, 'finance.payments.record');
    }
}
