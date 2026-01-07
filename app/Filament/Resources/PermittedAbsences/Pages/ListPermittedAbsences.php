<?php

namespace App\Filament\Resources\PermittedAbsences\Pages;

use App\Filament\Resources\PermittedAbsences\PermittedAbsenceResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPermittedAbsences extends ListRecords
{
    protected static string $resource = PermittedAbsenceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
