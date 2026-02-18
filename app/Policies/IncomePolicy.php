<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\Income;
use Illuminate\Auth\Access\HandlesAuthorization;

class IncomePolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $user): bool
    {
        return $user->hasRole(['super_admin', 'finance']);
    }

    public function view(AuthUser $user, Income $income): bool
    {
        return $user->hasRole(['super_admin', 'finance']);
    }

    public function create(AuthUser $user): bool
    {
        return $user->hasRole(['super_admin', 'finance']);
    }

    public function update(AuthUser $user, Income $income): bool
    {
        return $user->hasRole(['super_admin', 'finance']);
    }

    public function delete(AuthUser $user, Income $income): bool
    {
        return $user->hasRole(['super_admin', 'finance']);
    }

    public function restore(AuthUser $user, Income $income): bool
    {
        return $user->hasRole(['super_admin', 'finance']);
    }

    public function forceDelete(AuthUser $user, Income $income): bool
    {
        return $user->hasRole(['super_admin']);
    }
}
