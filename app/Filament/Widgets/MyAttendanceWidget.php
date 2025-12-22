<?php

namespace App\Filament\Widgets;

use App\Models\Attendance;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use Carbon\Carbon;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class MyAttendanceWidget extends BaseWidget
{
    use HasWidgetShield;

    protected int | string | array $columnSpan = 'full';

    protected static ?int $sort = 2;

    public function table(Table $table): Table
    {
        return $table
            ->heading('Absensi Saya Bulan Ini')
            ->query(
                Attendance::query()
                    ->where('user_id', auth()->id())
                    ->currentMonth()
                    ->orderBy('attendance_date', 'desc')
            )
            ->columns([
                Tables\Columns\TextColumn::make('attendance_date')
                    ->label('Tanggal')
                    ->date('d/m/Y (l)')
                    ->sortable(),

                Tables\Columns\TextColumn::make('check_in')
                    ->label('Check In')
                    ->dateTime('H:i')
                    ->badge()
                    ->color(fn ($record) => $record->isLate() ? 'danger' : 'success'),

                Tables\Columns\TextColumn::make('check_out')
                    ->label('Check Out')
                    ->dateTime('H:i')
                    ->badge()
                    ->color('info'),

                Tables\Columns\TextColumn::make('work_duration')
                    ->label('Durasi')
                    ->getStateUsing(function ($record) {
                        if (!$record->work_duration) {
                            return '-';
                        }
                        $hours = floor($record->work_duration / 60);
                        $minutes = $record->work_duration % 60;
                        return sprintf('%dj %dm', $hours, $minutes);
                    })
                    ->badge()
                    ->color('gray'),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->getStateUsing(fn ($record) => $record->status_label)
                    ->badge()
                    ->color(fn ($record) => $record->status_color),

                Tables\Columns\TextColumn::make('late_duration')
                    ->label('Keterlambatan')
                    ->getStateUsing(function ($record) {
                        if (!$record->late_duration) {
                            return '-';
                        }
                        return $record->late_duration . ' menit';
                    })
                    ->badge()
                    ->color('warning'),

                Tables\Columns\TextColumn::make('notes')
                    ->label('Catatan')
                    ->limit(50)
                    ->placeholder('-'),
            ])
            ->paginated([10, 25, 50]);
    }
}
