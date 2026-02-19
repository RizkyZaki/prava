<?php

namespace App\Filament\Resources\CashAccounts\Pages;

use App\Filament\Resources\CashAccounts\CashAccountResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCashAccount extends EditRecord
{
    protected static string $resource = CashAccountResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    public function handleRecordUpdate(\Illuminate\Database\Eloquent\Model $record, array $data): \Illuminate\Database\Eloquent\Model
    {
        $result = parent::handleRecordUpdate($record, $data);
        $record->recalculateBalance();
        return $result;
    }
}
