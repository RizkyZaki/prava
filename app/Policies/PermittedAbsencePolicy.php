<?php

namespace App\Policies;

use App\Models\PermittedAbsence;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class PermittedAbsencePolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        // Super admin can see all, users can see their own
        return true;
    }

    public function view(User $user, PermittedAbsence $permittedAbsence): bool
    {
        return $user->hasRole('super_admin') || $permittedAbsence->user_id === $user->id;
    }

    public function create(User $user): bool
    {
        return true; // All authenticated users can create
    }

    public function update(User $user, PermittedAbsence $permittedAbsence): bool
    {
        // Only pending absences can be updated, and only by the owner
        return $permittedAbsence->isPending() && $permittedAbsence->user_id === $user->id;
    }

    public function delete(User $user, PermittedAbsence $permittedAbsence): bool
    {
        // Only pending absences can be deleted
        return $user->hasRole('super_admin') ||
            ($permittedAbsence->isPending() && $permittedAbsence->user_id === $user->id);
    }

    public function approve(User $user, PermittedAbsence $permittedAbsence): bool
    {
        // Only super_admin can approve
        return $user->hasRole('super_admin') && $permittedAbsence->isPending();
    }

    public function reject(User $user, PermittedAbsence $permittedAbsence): bool
    {
        // Only super_admin can reject
        return $user->hasRole('super_admin') && $permittedAbsence->isPending();
    }
}
