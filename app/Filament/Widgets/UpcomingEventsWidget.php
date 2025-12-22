<?php

namespace App\Filament\Widgets;

use App\Models\Event;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class UpcomingEventsWidget extends BaseWidget
{
    use HasWidgetShield;

    protected int | string | array $columnSpan = 'full';

    protected static ?int $sort = 3;

    public function table(Table $table): Table
    {
        return $table
            ->heading('Kegiatan Akan Datang')
            ->query(
                Event::query()
                    ->upcoming()
                    ->limit(10)
            )
            ->columns([
                Tables\Columns\ColorColumn::make('color')
                    ->label('')
                    ->width('30px'),

                Tables\Columns\TextColumn::make('title')
                    ->label('Judul')
                    ->searchable()
                    ->limit(40),

                Tables\Columns\TextColumn::make('type')
                    ->label('Tipe')
                    ->getStateUsing(fn ($record) => $record->type_label)
                    ->badge()
                    ->color(fn ($record) => match ($record->type) {
                        'meeting' => 'info',
                        'deadline' => 'danger',
                        'holiday' => 'success',
                        'training' => 'warning',
                        'other' => 'gray',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('start_date')
                    ->label('Waktu Mulai')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),

                Tables\Columns\TextColumn::make('location')
                    ->label('Lokasi')
                    ->limit(30)
                    ->placeholder('-'),

                Tables\Columns\TextColumn::make('participants')
                    ->label('Peserta')
                    ->getStateUsing(function ($record) {
                        if (empty($record->participants)) {
                            return '-';
                        }
                        $count = count($record->participants);
                        return $count . ' orang';
                    })
                    ->badge()
                    ->color('gray'),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->getStateUsing(fn ($record) => $record->status_label)
                    ->badge()
                    ->color(fn ($record) => $record->status_color),
            ])
            ->actions([
                Tables\Actions\Action::make('view')
                    ->label('Lihat')
                    ->icon('heroicon-m-eye')
                    ->url(fn ($record) => route('filament.admin.resources.events.view', $record))
                    ->openUrlInNewTab(false),
            ])
            ->paginated(false);
    }
}
