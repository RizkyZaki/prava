<?php

namespace App\Filament\Resources\Incomes\Pages;

use App\Filament\Resources\Incomes\IncomeResource;
use Filament\Resources\Pages\EditRecord;

class EditIncome extends EditRecord
{
    protected static string $resource = IncomeResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        if (isset($data['amount'])) {
            $data['amount'] = (int) preg_replace('/[^0-9-]/', '', (string) $data['amount']);
        }

        return $data;
    }
}
