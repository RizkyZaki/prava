<?php

namespace App\Filament\Resources\Attendances\Pages;

use App\Filament\Resources\Attendances\AttendanceResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListAttendances extends ListRecords
{
    protected static string $resource = AttendanceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // CreateAction disabled - data otomatis dari mesin Hikvision
            // Actions\CreateAction::make()
            //     ->visible(fn () => auth()->user()->hasRole('super_admin')),
        ];
    }
}
