<?php

namespace App\Filament\Resources\CashAccounts\Pages;

use App\Filament\Resources\CashAccounts\CashAccountResource;
use Filament\Resources\Pages\CreateRecord;

class CreateCashAccount extends CreateRecord
{
    protected static string $resource = CashAccountResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (isset($data['initial_balance'])) {
            $data['initial_balance'] = (int) preg_replace('/[^0-9-]/', '', (string) $data['initial_balance']);
        }
        $data['current_balance'] = $data['initial_balance'] ?? 0;
        return $data;
    }
}
