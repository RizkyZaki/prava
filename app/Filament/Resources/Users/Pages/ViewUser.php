<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use Filament\Resources\Pages\ViewRecord;

class ViewUser extends ViewRecord
{
    protected static string $resource = UserResource::class;

    // set a custom breadcrumb/title if desired
    public function getTitle(): string
    {
        return 'User Details';
    }

    // render using our custom blade view
    protected string $view = 'filament.resources.users.view-user';
}
