<?php

namespace App\Filament\Resources\CashAccounts\Pages;

use App\Filament\Resources\CashAccounts\CashAccountResource;
use Filament\Resources\Pages\CreateRecord;

class CreateCashAccount extends CreateRecord
{
    protected static string $resource = CashAccountResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['current_balance'] = $data['initial_balance'] ?? 0;
        return $data;
    }
}
