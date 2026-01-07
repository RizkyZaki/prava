<?php

namespace App\Policies;

use App\Models\MonthlyPayroll;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class MonthlyPayrollPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        // Everyone can view (but query will be filtered in resource)
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, MonthlyPayroll $monthlyPayroll): bool
    {
        // Super admin can view all, regular users only their own
        return $user->hasRole('super_admin') || $monthlyPayroll->user_id === $user->id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // Only super admin can create
        return $user->hasRole('super_admin');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, MonthlyPayroll $monthlyPayroll): bool
    {
        // Only super admin can update
        return $user->hasRole('super_admin');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, MonthlyPayroll $monthlyPayroll): bool
    {
        // Only super admin can delete
        return $user->hasRole('super_admin');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, MonthlyPayroll $monthlyPayroll): bool
    {
        return $user->hasRole('super_admin');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, MonthlyPayroll $monthlyPayroll): bool
    {
        return $user->hasRole('super_admin');
    }
}
