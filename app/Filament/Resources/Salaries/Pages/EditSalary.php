<?php

namespace App\Filament\Resources\Salaries\Pages;

use App\Filament\Resources\Salaries\SalaryResource;
use App\Models\Salary;
use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditSalary extends EditRecord
{
    protected static string $resource = SalaryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Only check for duplicate if user_id or is_active changed
        if ($data['user_id'] !== $this->record->user_id ||
            ($data['is_active'] && !$this->record->is_active)) {

            $existingSalary = Salary::where('user_id', $data['user_id'])
                ->where('is_active', true)
                ->where('id', '!=', $this->record->id)
                ->whereDate('effective_from', '<=', now())
                ->where(function ($q) {
                    $q->whereNull('effective_to')
                        ->orWhereDate('effective_to', '>=', now());
                })
                ->first();

            if ($existingSalary) {
                Notification::make()
                    ->danger()
                    ->title('Duplicate Salary Configuration')
                    ->body('This employee already has an active salary configuration. Please deactivate it first.')
                    ->persistent()
                    ->send();

                $this->halt();
            }
        }

        return $data;
    }
}
