<?php

namespace App\Providers\Filament;

use Filament\Pages\Dashboard as FilamentDashboard;
use App\Filament\Pages\Dashboard;
use App\Filament\Pages\Auth\Login;
use BezhanSalleh\FilamentShield\FilamentShieldPlugin;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Filament\Support\Facades\FilamentView;
use Illuminate\Support\Facades\Blade;
use App\Models\Setting;
use App\Http\Middleware\FilamentUserSettings;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        $panel = $panel
            ->spa()
            ->databaseTransactions()
            ->default()
            ->id('admin')
            ->path('admin')
            ->login(Login::class)
            ->colors([
                'primary' => Color::Blue,
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
                FilamentUserSettings::class,
            ])
            ->plugins([
                FilamentShieldPlugin::make(),
            ])
            ->authMiddleware([
                Authenticate::class,
            ])
            ->passwordReset()
            ->emailVerification()
            // ->profile() // Profile feature disabled
            ->viteTheme('resources/css/filament/admin/theme.css');

        FilamentView::registerRenderHook(
            'panels::user-menu.before',
            fn () => view('filament.partials.language-toggle')
        );

        FilamentView::registerRenderHook(
            'panels::head.end',
            fn () => Blade::render(
                '<meta name="reverb-key" content="{{ $key }}">
                <meta name="reverb-host" content="{{ $host }}">
                <meta name="reverb-port" content="{{ $port }}">
                <meta name="reverb-scheme" content="{{ $scheme }}">',
                [
                    'key'    => config('broadcasting.connections.reverb.key'),
                    'host'   => config('broadcasting.connections.reverb.options.host'),
                    'port'   => config('broadcasting.connections.reverb.options.port'),
                    'scheme' => config('broadcasting.connections.reverb.options.scheme'),
                ]
            )
        );

        return $panel;
    }
}
