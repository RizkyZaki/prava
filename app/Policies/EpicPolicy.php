<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\Epic;
use App\Models\Project;
use Illuminate\Auth\Access\HandlesAuthorization;

class EpicPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('view_any_epic');
    }

    public function view(AuthUser $authUser, Epic $epic): bool
    {
        return $authUser->can('view_epic');
    }

    public function create(AuthUser $authUser, ?Project $project = null): bool
    {
        // Check basic permission
        if (!$authUser->can('create_epic')) {
            return false;
        }

        // If project is provided, check if user is a member of the project
        if ($project) {
            // Super admin can always create
            if ($authUser->hasRole(['super_admin'])) {
                return true;
            }

            // Check if user is a member of the project
            return $project->members()->where('users.id', $authUser->id)->exists();
        }

        return true;
    }

    public function update(AuthUser $authUser, Epic $epic): bool
    {
        // Check basic permission
        if (!$authUser->can('update_epic')) {
            return false;
        }

        // Super admin can always update
        if ($authUser->hasRole(['super_admin'])) {
            return true;
        }

        // Check if user is a member of the project
        return $epic->project->members()->where('users.id', $authUser->id)->exists();
    }

    public function delete(AuthUser $authUser, Epic $epic): bool
    {
        // Check basic permission
        if (!$authUser->can('delete_epic')) {
            return false;
        }

        // Super admin can always delete
        if ($authUser->hasRole(['super_admin'])) {
            return true;
        }

        // Check if user is a member of the project
        return $epic->project->members()->where('users.id', $authUser->id)->exists();
    }

    public function restore(AuthUser $authUser, Epic $epic): bool
    {
        return $authUser->can('restore_epic');
    }

    public function forceDelete(AuthUser $authUser, Epic $epic): bool
    {
        return $authUser->can('force_delete_epic');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('force_delete_any_epic');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('restore_any_epic');
    }

    public function replicate(AuthUser $authUser, Epic $epic): bool
    {
        return $authUser->can('replicate_epic');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('reorder_epic');
    }
}
