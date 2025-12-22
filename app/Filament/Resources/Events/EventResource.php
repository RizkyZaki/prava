<?php

namespace App\Filament\Resources\Events;

use Illuminate\Support\Facades\Auth;
use Filament\Schemas\Schema;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ColorColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Filament\Actions\ViewAction;
use Filament\Actions\EditAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use App\Filament\Resources\Events\Pages\ListEvents;
use App\Filament\Resources\Events\Pages\CreateEvent;
use App\Filament\Resources\Events\Pages\ViewEvent;
use App\Filament\Resources\Events\Pages\EditEvent;
use App\Models\Event;
use App\Models\User;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;

class EventResource extends Resource
{
    protected static ?string $model = Event::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-calendar';

    protected static ?string $navigationLabel = 'Kegiatan';

    protected static string|\UnitEnum|null $navigationGroup = 'Kalender & Kegiatan';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('title')
                    ->label('Judul Kegiatan')
                    ->required()
                    ->maxLength(255)
                    ->columnSpanFull(),

                RichEditor::make('description')
                    ->label('Deskripsi')
                    ->columnSpanFull(),

                Select::make('type')
                    ->label('Tipe Kegiatan')
                    ->options([
                        'meeting' => 'Rapat',
                        'deadline' => 'Deadline',
                        'holiday' => 'Libur',
                        'training' => 'Training',
                        'other' => 'Lainnya',
                    ])
                    ->required()
                    ->default('other'),

                ColorPicker::make('color')
                    ->label('Warna')
                    ->helperText('Warna untuk ditampilkan di kalender')
                    ->default('#3B82F6'),

                Toggle::make('all_day')
                    ->label('Seharian')
                    ->helperText('Event berlangsung sepanjang hari')
                    ->default(false)
                    ->live()
                    ->columnSpanFull(),

                DateTimePicker::make('start_date')
                    ->label('Tanggal/Waktu Mulai')
                    ->required()
                    ->seconds(false)
                    ->native(false)
                    ->displayFormat(fn ($get) => $get('all_day') ? 'd/m/Y' : 'd/m/Y H:i')
                    ->default(now()),

                DateTimePicker::make('end_date')
                    ->label('Tanggal/Waktu Selesai')
                    ->seconds(false)
                    ->native(false)
                    ->displayFormat(fn ($get) => $get('all_day') ? 'd/m/Y' : 'd/m/Y H:i')
                    ->afterOrEqual('start_date'),

                TextInput::make('location')
                    ->label('Lokasi')
                    ->maxLength(255)
                    ->columnSpanFull(),

                Select::make('participants')
                    ->label('Peserta')
                    ->multiple()
                    ->options(User::pluck('name', 'id')->toArray())
                    ->searchable()
                    ->preload()
                    ->helperText('Pilih user yang terlibat dalam kegiatan ini')
                    ->columnSpanFull(),

                Select::make('status')
                    ->label('Status')
                    ->options([
                        'scheduled' => 'Terjadwal',
                        'ongoing' => 'Berlangsung',
                        'completed' => 'Selesai',
                        'cancelled' => 'Dibatalkan',
                    ])
                    ->default('scheduled')
                    ->required(),

                Textarea::make('notes')
                    ->label('Catatan')
                    ->rows(3)
                    ->columnSpanFull(),

                Select::make('created_by')
                    ->label('Dibuat Oleh')
                    ->relationship('creator', 'name')
                    ->default(fn () => Auth::id())
                    ->disabled()
                    ->dehydrated()
                    ->hiddenOn('create'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ColorColumn::make('color')
                    ->label('')
                    ->width('40px'),

                TextColumn::make('title')
                    ->label('Judul')
                    ->searchable()
                    ->sortable()
                    ->limit(50),

                TextColumn::make('type')
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
                    })
                    ->sortable(),

                TextColumn::make('start_date')
                    ->label('Tanggal Mulai')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),

                TextColumn::make('end_date')
                    ->label('Tanggal Selesai')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->placeholder('-'),

                TextColumn::make('location')
                    ->label('Lokasi')
                    ->limit(30)
                    ->placeholder('-')
                    ->toggleable(),

                TextColumn::make('participants')
                    ->label('Peserta')
                    ->getStateUsing(function ($record) {
                        if (empty($record->participants)) {
                            return '-';
                        }
                        return count($record->participants) . ' orang';
                    })
                    ->badge()
                    ->color('gray')
                    ->toggleable(),

                TextColumn::make('status')
                    ->label('Status')
                    ->getStateUsing(fn ($record) => $record->status_label)
                    ->badge()
                    ->color(fn ($record) => $record->status_color)
                    ->sortable(),

                TextColumn::make('creator.name')
                    ->label('Dibuat Oleh')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('start_date', 'asc')
            ->filters([
                SelectFilter::make('type')
                    ->label('Tipe')
                    ->options([
                        'meeting' => 'Rapat',
                        'deadline' => 'Deadline',
                        'holiday' => 'Libur',
                        'training' => 'Training',
                        'other' => 'Lainnya',
                    ]),

                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'scheduled' => 'Terjadwal',
                        'ongoing' => 'Berlangsung',
                        'completed' => 'Selesai',
                        'cancelled' => 'Dibatalkan',
                    ]),

                Filter::make('upcoming')
                    ->label('Akan Datang')
                    ->query(fn (Builder $query): Builder => $query->upcoming())
                    ->toggle(),

                Filter::make('today')
                    ->label('Hari Ini')
                    ->query(fn (Builder $query): Builder => $query->today())
                    ->toggle(),

                Filter::make('date_range')
                    ->form([
                        DateTimePicker::make('from')
                            ->label('Dari Tanggal')
                            ->native(false),
                        DateTimePicker::make('until')
                            ->label('Sampai Tanggal')
                            ->native(false),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['from'],
                                fn (Builder $query, $date): Builder => $query->where('start_date', '>=', $date),
                            )
                            ->when(
                                $data['until'],
                                fn (Builder $query, $date): Builder => $query->where('start_date', '<=', $date),
                            );
                    })
                    ->indicateUsing(function (array $data): ?string {
                        if (!$data['from'] && !$data['until']) {
                            return null;
                        }

                        return 'Tanggal: ' .
                            ($data['from'] ? Carbon::parse($data['from'])->format('d/m/Y') : '...') .
                            ' - ' .
                            ($data['until'] ? Carbon::parse($data['until'])->format('d/m/Y') : '...');
                    }),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make()
                    ->visible(fn () => Auth::user()->hasRole('super_admin')),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->visible(fn () => Auth::user()->hasRole('super_admin')),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListEvents::route('/'),
            'create' => CreateEvent::route('/create'),
            'view' => ViewEvent::route('/{record}'),
            'edit' => EditEvent::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return (string) Event::upcoming()->count();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'success';
    }
}
