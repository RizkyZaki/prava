<?php

namespace App\Filament\Pages;

use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    protected string $view = 'filament.pages.dashboard';

    public function getViewData(): array
    {
        $user = auth()->user();
        $isSuperAdmin = $user->hasRole('super_admin');

        return [
            'user' => $user,
            'isSuperAdmin' => $isSuperAdmin,
        ];
    }
}
