<?php

namespace App\Filament\Resources\Salaries\Pages;

use App\Filament\Resources\Salaries\SalaryResource;
use App\Models\Salary;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreateSalary extends CreateRecord
{
    protected static string $resource = SalaryResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Check if user already has an active salary
        $existingSalary = Salary::where('user_id', $data['user_id'])
            ->where('is_active', true)
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
                ->body('This employee already has an active salary configuration. Please edit the existing one or deactivate it first.')
                ->persistent()
                ->send();

            $this->halt();
        }

        return $data;
    }
}
