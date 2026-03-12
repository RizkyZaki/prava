<?php

namespace App\Filament\Pages;

use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Filament\Pages\Page;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\Auth;

class WhatsappDashboard extends Page
{
    use HasPageShield;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-chat-bubble-left-right';

    public function getTitle(): string|Htmlable
    {
        return __('page.whatsapp_customer_service');
    }

    public static function getNavigationLabel(): string
    {
        return __('page.whatsapp_cs');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('nav.group.customer_service');
    }
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
