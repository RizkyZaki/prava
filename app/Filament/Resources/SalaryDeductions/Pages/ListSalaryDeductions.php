<?php

namespace App\Filament\Resources\SalaryDeductions\Pages;

use App\Filament\Resources\SalaryDeductions\SalaryDeductionResource;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class ListSalaryDeductions extends ListRecords
{
    protected static string $resource = SalaryDeductionResource::class;

    public function getTableQuery(): Builder
    {
        $query = parent::getTableQuery();

        // Filter by user if not super_admin
        if (!Auth::user()->hasRole('super_admin')) {
            $query->where('user_id', Auth::id());
        }

        return $query;
    }
}
