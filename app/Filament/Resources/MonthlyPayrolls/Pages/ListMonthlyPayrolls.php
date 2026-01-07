<?php

namespace App\Filament\Resources\MonthlyPayrolls\Pages;

use App\Filament\Resources\MonthlyPayrolls\MonthlyPayrollResource;
use Filament\Resources\Pages\ListRecords;

class ListMonthlyPayrolls extends ListRecords
{
    protected static string $resource = MonthlyPayrollResource::class;
}
