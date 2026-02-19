<?php

namespace App\Filament\Resources\Expenses\Pages;

use App\Filament\Resources\Expenses\ExpenseResource;
use Filament\Resources\Pages\CreateRecord;

class CreateExpense extends CreateRecord
{
    protected static string $resource = ExpenseResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // sanitize amount formatting (e.g. 1.000.000 -> 1000000)
        if (isset($data['amount'])) {
            $data['amount'] = (int) preg_replace('/[^0-9-]/', '', (string) $data['amount']);
        }

        $data['created_by'] = auth()->id();
        return $data;
    }
}
