<?php

namespace App\Filament\Resources\SalaryDeductions;

use App\Filament\Resources\SalaryDeductions\Pages\ListSalaryDeductions;
use App\Models\SalaryDeduction;
use Filament\Resources\Resource;
use Filament\Actions\Action;
use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class SalaryDeductionResource extends Resource
{
    protected static ?string $model = SalaryDeduction::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-currency-dollar';

    protected static ?string $navigationLabel = 'Salary Deductions';

    protected static ?int $navigationSort = 3;

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('deduction_date')
                    ->label('Date')
                    ->date('d M Y')
                    ->sortable(),

                TextColumn::make('user.name')
                    ->label('Employee')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('deduction_type_label')
                    ->label('Type')
                    ->badge()
                    ->color(fn (SalaryDeduction $record) => match($record->deduction_type) {
                        'late' => 'warning',
                        'early_leave' => 'info',
                        'absent', 'no_check_in', 'no_check_out' => 'danger',
                        'short_hours' => 'warning',
                        'manual' => 'gray',
                        default => 'gray',
                    }),

                TextColumn::make('deduction_amount')
                    ->label('Amount')
                    ->money('IDR')
                    ->sortable(),

                TextColumn::make('minutes_late')
                    ->label('Late (min)')
                    ->default('-')
                    ->alignCenter(),

                TextColumn::make('minutes_early')
                    ->label('Early (min)')
                    ->default('-')
                    ->alignCenter(),

                TextColumn::make('reason')
                    ->label('Reason')
                    ->wrap()
                    ->limit(50),

                IconColumn::make('is_approved')
                    ->label('Approved')
                    ->boolean()
                    ->alignCenter(),

                TextColumn::make('approvedBy.name')
                    ->label('Approved By')
                    ->default('-'),
            ])
            ->filters([
                SelectFilter::make('user_id')
                    ->label('Employee')
                    ->relationship('user', 'name')
                    ->searchable(),

                SelectFilter::make('deduction_type')
                    ->label('Type')
                    ->options([
                        'late' => 'Terlambat',
                        'early_leave' => 'Pulang Cepat',
                        'absent' => 'Tidak Masuk',
                        'no_check_in' => 'Tidak Check In',
                        'no_check_out' => 'Tidak Check Out',
                        'short_hours' => 'Jam Kerja Kurang',
                        'manual' => 'Manual',
                    ]),

                SelectFilter::make('is_approved')
                    ->label('Status')
                    ->options([
                        '1' => 'Approved',
                        '0' => 'Pending',
                    ]),

                Filter::make('deduction_date')
                    ->form([
                        \Filament\Forms\Components\DatePicker::make('from')
                            ->label('From'),
                        \Filament\Forms\Components\DatePicker::make('to')
                            ->label('To'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('deduction_date', '>=', $date),
                            )
                            ->when(
                                $data['to'],
                                fn (Builder $query, $date): Builder => $query->whereDate('deduction_date', '<=', $date),
                            );
                    }),
            ])
            ->actions([
                Action::make('approve')
                    ->label('Approve')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (SalaryDeduction $record) => !$record->is_approved && Auth::user()->hasRole('super_admin'))
                    ->requiresConfirmation()
                    ->action(function (SalaryDeduction $record) {
                        $record->update([
                            'is_approved' => true,
                            'approved_by' => Auth::id(),
                            'approved_at' => now(),
                        ]);
                    }),

                Action::make('view_details')
                    ->label('Details')
                    ->icon('heroicon-o-eye')
                    ->modalHeading('Deduction Details')
                    ->modalContent(fn (SalaryDeduction $record) => view('filament.modals.deduction-details', ['record' => $record]))
                    ->modalSubmitAction(false),
            ])
            ->bulkActions([
                BulkAction::make('approve_selected')
                    ->label('Approve Selected')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn () => Auth::user()->hasRole('super_admin'))
                    ->requiresConfirmation()
                    ->deselectRecordsAfterCompletion()
                    ->action(function (\Illuminate\Support\Collection $records) {
                        foreach ($records as $record) {
                            $record->update([
                                'is_approved' => true,
                                'approved_by' => Auth::id(),
                                'approved_at' => now(),
                            ]);
                        }
                    }),
            ])
            ->defaultSort('deduction_date', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSalaryDeductions::route('/'),
        ];
    }
}
