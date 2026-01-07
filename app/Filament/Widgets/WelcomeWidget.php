<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Auth;

class WelcomeWidget extends Widget
{
    protected string $view = 'filament.widgets.welcome-widget';

    protected int | string | array $columnSpan = 'full';

    protected static ?int $sort = 0;

    protected static ?string $pollingInterval = null;

    public function getViewData(): array
    {
        $user = Auth::user();
        $hour = now()->hour;

        if ($hour < 12) {
            $greeting = 'Selamat Pagi';
            $icon = '🌅';
        } elseif ($hour < 15) {
            $greeting = 'Selamat Siang';
            $icon = '☀️';
        } elseif ($hour < 18) {
            $greeting = 'Selamat Sore';
            $icon = '🌤️';
        } else {
            $greeting = 'Selamat Malam';
            $icon = '🌙';
        }

        return [
            'greeting' => $greeting,
            'icon' => $icon,
            'userName' => $user->name,
            'role' => $user->hasRole('super_admin') ? 'Administrator' : 'Karyawan',
            'currentDate' => now()->isoFormat('dddd, D MMMM YYYY'),
        ];
    }
}
