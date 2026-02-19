<?php

namespace App\Filament\Resources\Events\Pages;

use App\Filament\Resources\Events\EventResource;
use Filament\Resources\Pages\CreateRecord;

class CreateEvent extends CreateRecord
{
    public function mount(): void
    {
        parent::mount();
        if (!auth()->user() || !auth()->user()->hasRole('super_admin')) {
            abort(403, 'Hanya super admin yang bisa menambah event');
        }
    }
    protected static string $resource = EventResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
