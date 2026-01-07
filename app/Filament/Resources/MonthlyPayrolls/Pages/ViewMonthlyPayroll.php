<?php

namespace App\Filament\Resources\MonthlyPayrolls\Pages;

use App\Filament\Resources\MonthlyPayrolls\MonthlyPayrollResource;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\TextEntry;
use Filament\Schemas\Schema;

class ViewMonthlyPayroll extends ViewRecord
{
    protected static string $resource = MonthlyPayrollResource::class;

    public function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Period & Employee')
                    ->schema([
                        TextEntry::make('period_label')
                            ->label('Period'),
                        TextEntry::make('user.name')
                            ->label('Employee'),
                        TextEntry::make('status_label')
                            ->label('Status')
                            ->badge()
                            ->color(fn ($record) => match($record->status) {
                                'draft' => 'gray',
                                'calculated' => 'warning',
                                'approved' => 'success',
                                'paid' => 'primary',
                                default => 'gray',
                            }),
                    ])
                    ->columns(3),

                Section::make('Salary Components')
                    ->schema([
                        TextEntry::make('base_salary')
                            ->label('Base Salary')
                            ->money('IDR'),
                        TextEntry::make('transport_allowance')
                            ->label('Transport')
                            ->money('IDR'),
                        TextEntry::make('meal_allowance')
                            ->label('Meal')
                            ->money('IDR'),
                        TextEntry::make('position_allowance')
                            ->label('Position')
                            ->money('IDR'),
                        TextEntry::make('other_allowance')
                            ->label('Other')
                            ->money('IDR'),
                        TextEntry::make('gross_salary')
                            ->label('Gross Salary')
                            ->money('IDR')
                            ->weight('bold'),
                    ])
                    ->columns(3),

                Section::make('Attendance Summary')
                    ->schema([
                        TextEntry::make('total_days_present')
                            ->label('Days Present'),
                        TextEntry::make('total_days_late')
                            ->label('Days Late'),
                        TextEntry::make('total_days_absent')
                            ->label('Days Absent'),
                        TextEntry::make('total_work_minutes')
                            ->label('Total Work Hours')
                            ->formatStateUsing(fn ($state) => round($state / 60, 2) . ' hours'),
                    ])
                    ->columns(4),

                Section::make('Deductions')
                    ->schema([
                        TextEntry::make('late_deductions')
                            ->label('Late Deductions')
                            ->money('IDR'),
                        TextEntry::make('early_leave_deductions')
                            ->label('Early Leave')
                            ->money('IDR'),
                        TextEntry::make('absent_deductions')
                            ->label('Absent')
                            ->money('IDR'),
                        TextEntry::make('other_deductions')
                            ->label('Other')
                            ->money('IDR'),
                        TextEntry::make('total_deductions')
                            ->label('Total Deductions')
                            ->money('IDR')
                            ->weight('bold')
                            ->color('danger'),
                    ])
                    ->columns(3),

                Section::make('Net Salary')
                    ->schema([
                        TextEntry::make('net_salary')
                            ->label('Net Salary (Gross - Deductions)')
                            ->money('IDR')
                            ->size('lg')
                            ->weight('bold')
                            ->color('success'),
                    ]),

                Section::make('Payment Information')
                    ->schema([
                        TextEntry::make('payment_date')
                            ->label('Payment Date')
                            ->date('d F Y')
                            ->default('-'),
                        TextEntry::make('approvedBy.name')
                            ->label('Approved By')
                            ->default('-'),
                        TextEntry::make('approved_at')
                            ->label('Approved At')
                            ->dateTime('d F Y H:i')
                            ->default('-'),
                        TextEntry::make('notes')
                            ->label('Notes')
                            ->default('-')
                            ->columnSpanFull(),
                    ])
                    ->columns(3),
            ]);
    }
}
