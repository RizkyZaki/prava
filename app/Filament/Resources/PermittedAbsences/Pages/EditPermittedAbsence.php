<?php

namespace App\Filament\Resources\PermittedAbsences\Pages;

use App\Filament\Resources\PermittedAbsences\PermittedAbsenceResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditPermittedAbsence extends EditRecord
{
    protected static string $resource = PermittedAbsenceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
