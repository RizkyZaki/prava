<?php

namespace App\Filament\Resources\MonthlyPayrolls;

use App\Filament\Resources\MonthlyPayrolls\Pages\ListMonthlyPayrolls;
use App\Filament\Resources\MonthlyPayrolls\Pages\ViewMonthlyPayroll;
use App\Models\MonthlyPayroll;
use App\Services\MonthlyPayrollService;
use Filament\Resources\Resource;
use Filament\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class MonthlyPayrollResource extends Resource
{
    protected static ?string $model = MonthlyPayroll::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-document-currency-dollar';

    protected static ?string $navigationLabel = 'Monthly Payrolls';

    protected static ?int $navigationSort = 4;

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        $query = parent::getEloquentQuery();

        // If not super admin, only show user's own payrolls
        if (!Auth::user()->hasRole('super_admin')) {
            $query->where('user_id', Auth::id());
        }

        return $query;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('period_label')
                    ->label('Period')
                    ->sortable(['year', 'month'])
                    ->searchable(false),

                TextColumn::make('user.name')
                    ->label('Employee')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('gross_salary')
                    ->label('Gross Salary')
                    ->money('IDR')
                    ->sortable(),

                TextColumn::make('total_deductions')
                    ->label('Deductions')
                    ->money('IDR')
                    ->color('danger')
                    ->sortable(),

                TextColumn::make('net_salary')
                    ->label('Net Salary')
                    ->money('IDR')
                    ->weight('bold')
                    ->color('success')
                    ->sortable(),

                TextColumn::make('total_days_present')
                    ->label('Days Present')
                    ->alignCenter(),

                TextColumn::make('total_days_late')
                    ->label('Days Late')
                    ->alignCenter()
                    ->color('warning'),

                TextColumn::make('status_label')
                    ->label('Status')
                    ->badge()
                    ->color(fn (MonthlyPayroll $record) => match($record->status) {
                        'draft' => 'gray',
                        'calculated' => 'warning',
                        'approved' => 'success',
                        'paid' => 'primary',
                        default => 'gray',
                    }),
            ])
            ->filters([
                SelectFilter::make('user_id')
                    ->label('Employee')
                    ->relationship('user', 'name')
                    ->searchable(),

                SelectFilter::make('year')
                    ->label('Year')
                    ->options(function () {
                        $currentYear = now()->year;
                        return collect(range($currentYear - 2, $currentYear + 1))
                            ->mapWithKeys(fn($year) => [$year => $year]);
                    }),

                SelectFilter::make('month')
                    ->label('Month')
                    ->options([
                        1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
                        5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
                        9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
                    ]),

                SelectFilter::make('status')
                    ->options([
                        'draft' => 'Draft',
                        'calculated' => 'Calculated',
                        'approved' => 'Approved',
                        'paid' => 'Paid',
                    ]),
            ])
            ->actions([
                Action::make('approve')
                    ->label('Approve')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (MonthlyPayroll $record) =>
                        $record->status === 'calculated' && Auth::user()->hasRole('super_admin')
                    )
                    ->requiresConfirmation()
                    ->action(function (MonthlyPayroll $record) {
                        app(MonthlyPayrollService::class)->approvePayroll($record, Auth::id());
                    }),

                Action::make('mark_as_paid')
                    ->label('Mark as Paid')
                    ->icon('heroicon-o-banknotes')
                    ->color('primary')
                    ->visible(fn (MonthlyPayroll $record) =>
                        $record->status === 'approved' && Auth::user()->hasRole('super_admin')
                    )
                    ->requiresConfirmation()
                    ->action(function (MonthlyPayroll $record) {
                        app(MonthlyPayrollService::class)->markAsPaid($record);
                    }),
            ])
            ->headerActions([
                Action::make('generate_payroll')
                    ->label('Generate This Month')
                    ->icon('heroicon-o-arrow-path')
                    ->color('primary')
                    ->visible(fn () => Auth::user()->hasRole('super_admin'))
                    ->requiresConfirmation()
                    ->action(function () {
                        $service = app(MonthlyPayrollService::class);
                        $results = $service->generateBulkPayroll(now()->year, now()->month);

                        \Filament\Notifications\Notification::make()
                            ->title('Payroll Generated')
                            ->body("Success: {$results['success']}, Failed: {$results['failed']}")
                            ->success()
                            ->send();
                    }),
            ])
            ->defaultSort('year', 'desc')
            ->defaultSort('month', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => ListMonthlyPayrolls::route('/'),
            'view' => ViewMonthlyPayroll::route('/{record}'),
        ];
    }
}
