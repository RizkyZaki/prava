<?php

namespace App\Filament\Resources\PermittedAbsences\Tables;

use App\Models\SalaryDeduction;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class PermittedAbsencesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.name')
                    ->label('Karyawan')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('absence_type')
                    ->label('Jenis')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'izin' => 'warning',
                        'sakit' => 'danger',
                        'remote' => 'info',
                    })
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'izin' => 'Izin',
                        'sakit' => 'Sakit',
                        'remote' => 'Remote',
                    }),

                TextColumn::make('start_date')
                    ->label('Dari')
                    ->date('d M Y')
                    ->sortable(),

                TextColumn::make('end_date')
                    ->label('Sampai')
                    ->date('d M Y')
                    ->sortable(),

                TextColumn::make('reason')
                    ->label('Alasan')
                    ->limit(30)
                    ->tooltip(fn($record) => $record->reason),

                ImageColumn::make('attachment')
                    ->label('Lampiran')
                    ->size(40)
                    ->defaultImageUrl(url('/images/no-image.png'))
                    ->toggleable(),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'pending' => 'warning',
                        'approved' => 'success',
                        'rejected' => 'danger',
                    })
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'pending' => 'Menunggu',
                        'approved' => 'Disetujui',
                        'rejected' => 'Ditolak',
                    }),

                TextColumn::make('approver.name')
                    ->label('Disetujui Oleh')
                    ->toggleable()
                    ->placeholder('-'),

                TextColumn::make('approved_at')
                    ->label('Tanggal Approval')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable()
                    ->placeholder('-'),

                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('absence_type')
                    ->label('Jenis')
                    ->options([
                        'izin' => 'Izin',
                        'sakit' => 'Sakit',
                        'remote' => 'Remote',
                    ]),

                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'pending' => 'Menunggu',
                        'approved' => 'Disetujui',
                        'rejected' => 'Ditolak',
                    ]),
            ])
            ->recordActions([
                Action::make('approve')
                    ->label('Setujui')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->action(function ($record) {
                        $record->update([
                            'status' => 'approved',
                            'approved_by' => Auth::id(),
                            'approved_at' => now(),
                            'rejection_reason' => null,
                        ]);

                        // Auto-reject/delete salary deductions for approved absence dates
                        $startDate = Carbon::parse($record->start_date);
                        $endDate = Carbon::parse($record->end_date);

                        $deletedCount = SalaryDeduction::where('user_id', $record->user_id)
                            ->whereBetween('deduction_date', [$startDate, $endDate])
                            ->delete();

                        $message = "Perizinan {$record->user->name} telah disetujui.";
                        if ($deletedCount > 0) {
                            $message .= " {$deletedCount} potongan gaji otomatis dihapus.";
                        }

                        Notification::make()
                            ->success()
                            ->title('Perizinan Disetujui')
                            ->body($message)
                            ->send();
                    })
                    ->visible(fn($record) => $record->status === 'pending' && Auth::user()->hasRole('super_admin')),

                Action::make('reject')
                    ->label('Tolak')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->form([
                        \Filament\Forms\Components\Textarea::make('rejection_reason')
                            ->label('Alasan Penolakan')
                            ->required()
                            ->rows(3),
                    ])
                    ->action(function ($record, array $data) {
                        $record->update([
                            'status' => 'rejected',
                            'approved_by' => Auth::id(),
                            'approved_at' => now(),
                            'rejection_reason' => $data['rejection_reason'],
                        ]);

                        Notification::make()
                            ->danger()
                            ->title('Perizinan Ditolak')
                            ->body("Perizinan {$record->user->name} telah ditolak.")
                            ->send();
                    })
                    ->visible(fn($record) => $record->status === 'pending' && Auth::user()->hasRole('super_admin')),

                EditAction::make()
                    ->visible(fn($record) => $record->status === 'pending'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->modifyQueryUsing(function ($query) {
                // Non-admin only see their own
                if (!Auth::user()->hasRole('super_admin')) {
                    $query->where('user_id', Auth::id());
                }
                return $query;
            });
    }
}
