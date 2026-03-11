<?php

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use Illuminate\Auth\Access\HandlesAuthorization;

class WhatsappConversationPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('view_any_whatsapp::conversation');
    }

    public function view(AuthUser $authUser): bool
    {
        return $authUser->can('view_whatsapp::conversation');
    }

    public function create(AuthUser $authUser): bool
    {
        return false;
    }

    public function update(AuthUser $authUser): bool
    {
        return false;
    }

    public function delete(AuthUser $authUser): bool
    {
        return $authUser->can('delete_whatsapp::conversation');
    }

    public function restore(AuthUser $authUser): bool
    {
        return false;
    }

    public function forceDelete(AuthUser $authUser): bool
    {
        return false;
    }
}
