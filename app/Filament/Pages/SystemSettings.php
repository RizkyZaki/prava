<?php

namespace App\Filament\Pages;

use Filament\Forms\Components\Radio;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Contracts\Support\Htmlable;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Schemas\Schema;
use BackedEnum;
use UnitEnum;
use Filament\Support\Icons\Heroicon;
use Filament\Support\Colors\Color;
use Filament\Support\Facades\FilamentColor;
use Filament\Actions\Action;
use App\Models\Setting;
use App\Support\ColorPalette;

class SystemSettings extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::Cog6Tooth;

    public function getTitle(): string|Htmlable
    {
        return __('page.ui_settings');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('nav.group.settings');
    }
    protected string $view = 'filament.pages.system-settings';

    public ?array $data = [];

    public function mount(): void
    {
        $userId = auth()->id();

        $this->form->fill([
            'navigation_style' => Setting::getUserValue('filament_navigation_style', 'sidebar', $userId),
            'panel_color' => Setting::getUserValue('filament_primary_color', 'blue', $userId),
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('page.layout_style'))
                    ->description(__('page.layout_style_desc'))
                    ->icon('heroicon-o-bars-3')
                    ->schema([
                        Radio::make('navigation_style')
                            ->label(__('page.layout_style'))
                            ->options([
                                'sidebar' => __('page.sidebar_navigation'),
                                'top' => __('page.top_navigation'),
                            ])
                            ->descriptions([
                                'sidebar' => __('page.sidebar_navigation_desc'),
                                'top' => __('page.top_navigation_desc'),
                            ])
                            ->inline(false)
                            ->required()
                            ->live()
                            ->afterStateUpdated(function ($state) {
                                $this->updateNavigationStyle($state);
                            }),
                    ]),

                Section::make(__('page.color_theme'))
                    ->description(__('page.color_theme_desc'))
                    ->icon('heroicon-o-swatch')
                    ->schema([
                        Select::make('panel_color')
                            ->label(__('page.primary_color'))
                            ->options(ColorPalette::options())
                            ->required()
                            ->live()
                            ->afterStateUpdated(function ($state) {
                                $this->updateColorTheme($state);
                            }),
                    ]),
            ])
            ->statePath('data');
    }

    protected function updateNavigationStyle(string $style): void
    {
        Setting::setUserValue('filament_navigation_style', $style, 'ui', auth()->id());

        $this->dispatch('navigation-style-updated', style: $style);

        Notification::make()
            ->title(__('page.navigation_updated'))
            ->body($style === 'top'
                ? __('page.top_navigation_saved')
                : __('page.sidebar_navigation_saved'))
            ->success()
            ->send();
    }

    protected function updateColorTheme(string $color): void
    {
        Setting::setUserValue('filament_primary_color', $color, 'ui', auth()->id());

        $this->applyColorChange($color);

        $this->dispatch('color-theme-updated', color: $color);

        Notification::make()
            ->title(__('page.color_theme_updated'))
            ->body(__('page.primary_color_changed', ['color' => $color]))
            ->success()
            ->send();
    }

    protected function applyColorChange(string $colorName): void
    {
        FilamentColor::register([
            'primary' => ColorPalette::constantFor($colorName),
        ]);
    }

    public function save(): void
    {
        $this->updateNavigationStyle($this->data['navigation_style']);
        $this->updateColorTheme($this->data['panel_color']);

        Notification::make()
            ->title('Settings Saved Successfully')
            ->body('Preferences saved. Reloading to apply layout...')
            ->success()
            ->send();

        $this->dispatch('settings-saved');
    }
}
