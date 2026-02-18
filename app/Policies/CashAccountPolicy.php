<?php

namespace App\Policies;

use App\Models\CashAccount;
use App\Models\User;

class CashAccountPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole(['super_admin', 'finance']);
    }

    public function view(User $user, CashAccount $cashAccount): bool
    {
        return $user->hasRole(['super_admin', 'finance']);
    }

    public function create(User $user): bool
    {
        return $user->hasRole(['super_admin', 'finance']);
    }

    public function update(User $user, CashAccount $cashAccount): bool
    {
        return $user->hasRole(['super_admin', 'finance']);
    }

    public function delete(User $user, CashAccount $cashAccount): bool
    {
        return $user->hasRole(['super_admin', 'finance']);
    }
}
