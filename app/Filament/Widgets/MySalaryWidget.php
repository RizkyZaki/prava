<?php

namespace App\Filament\Widgets;

use App\Models\MonthlyPayroll;
use App\Models\Salary;
use App\Models\SalaryDeduction;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Auth;

class MySalaryWidget extends Widget
{
    protected string $view = 'filament.widgets.my-salary-widget';

    protected int | string | array $columnSpan = [
        'default' => 'full',
        'sm' => 'full',
        'md' => 'full',
        'lg' => 2,
        'xl' => 2,
        '2xl' => 2,
    ];

    protected static ?int $sort = 1;

    protected static ?string $pollingInterval = null;

    public function getData(): array
    {
        $user = Auth::user();
        $currentMonth = now()->month;
        $currentYear = now()->year;

        // Get active salary
        $salary = Salary::where('user_id', $user->id)
            ->where('is_active', true)
            ->whereDate('effective_from', '<=', now())
            ->where(function ($q) {
                $q->whereNull('effective_to')
                    ->orWhereDate('effective_to', '>=', now());
            })
            ->first();

        // Get this month's payroll
        $monthlyPayroll = MonthlyPayroll::where('user_id', $user->id)
            ->where('year', $currentYear)
            ->where('month', $currentMonth)
            ->first();

        // Get this month's deductions
        $monthlyDeductions = SalaryDeduction::where('user_id', $user->id)
            ->whereYear('deduction_date', $currentYear)
            ->whereMonth('deduction_date', $currentMonth)
            ->get();

        return [
            'salary' => $salary,
            'monthlyPayroll' => $monthlyPayroll,
            'monthlyDeductions' => $monthlyDeductions,
            'currentMonth' => now()->format('F Y'),
        ];
    }
}
