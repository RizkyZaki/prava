<?php

namespace App\Policies;

use App\Models\SalaryDeduction;
use App\Models\User;

class SalaryDeductionPolicy
{
    public function viewAny(User $user): bool
    {
        // Super admin can see all, regular users see their own
        return true;
    }

    public function view(User $user, SalaryDeduction $salaryDeduction): bool
    {
        // Super admin can view all, users can only view their own
        return $user->hasRole('super_admin') || $salaryDeduction->user_id === $user->id;
    }

    public function create(User $user): bool
    {
        // Only super admin can create manual deductions
        return $user->hasRole('super_admin');
    }

    public function update(User $user, SalaryDeduction $salaryDeduction): bool
    {
        // Only super admin can update
        return $user->hasRole('super_admin');
    }

    public function delete(User $user, SalaryDeduction $salaryDeduction): bool
    {
        // Only super admin can delete
        return $user->hasRole('super_admin');
    }

    public function approve(User $user, SalaryDeduction $salaryDeduction): bool
    {
        // Only super admin can approve
        return $user->hasRole('super_admin');
    }
}
