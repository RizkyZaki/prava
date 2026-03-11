<?php

namespace App\Filament\Pages;

use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;

class WhatsappDashboard extends Page
{
    use HasPageShield;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-chat-bubble-left-right';
    protected static string|\UnitEnum|null $navigationGroup = 'Customer Service';
    protected static ?string $navigationLabel = 'WhatsApp CS';
    protected static ?string $title = 'WhatsApp Customer Service';
    protected static ?int $navigationSort = 1;
    protected static bool $shouldRegisterNavigation = true;

    protected string $view = 'filament.pages.whatsapp-dashboard';

    public function mount(): void
    {
        $this->redirect(route('whatsapp.chat'), navigate: false);
    }

    public static function canAccess(): bool
    {
        $user = Auth::user();
        return $user && $user->hasRole('super_admin');
    }
}
