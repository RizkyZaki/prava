<?php

namespace App\Filament\Resources\Attendances;

use Illuminate\Support\Facades\Auth;
use Filament\Schemas\Schema;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Filament\Actions\ViewAction;
use Filament\Actions\EditAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ExportAction;
use App\Filament\Resources\Attendances\Pages\ListAttendances;
use App\Filament\Resources\Attendances\Pages\CreateAttendance;
use App\Filament\Resources\Attendances\Pages\EditAttendance;
use App\Models\Attendance;
use App\Models\User;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;

class AttendanceResource extends Resource
{
    protected static ?string $model = Attendance::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-clock';

    protected static ?string $navigationLabel = 'Attendance';

    protected static string|\UnitEnum|null $navigationGroup = 'Human Resources';

    protected static ?int $navigationSort = 1;

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        // Non-admin hanya bisa lihat attendance mereka sendiri
        if (!Auth::user()->hasRole(['super_admin'])) {
            $query->where('user_id', Auth::id());
        }

        return $query;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('user_id')
                    ->label('User')
                    ->options(User::pluck('name', 'id')->toArray())
                    ->required()
                    ->searchable()
                    ->preload()
                    ->default(fn () => Auth::id())
                    ->disabled(fn ($livewire) => $livewire instanceof EditAttendance || !Auth::user()->hasRole('super_admin')),

                DatePicker::make('attendance_date')
                    ->label('Tanggal')
                    ->required()
                    ->default(today())
                    ->native(false)
                    ->displayFormat('d/m/Y')
                    ->maxDate(today()),

                DateTimePicker::make('check_in')
                    ->label('Check In (Jam Masuk)')
                    ->seconds(false)
                    ->native(false)
                    ->displayFormat('d/m/Y H:i')
                    ->helperText('Jam masuk standar: 08:00'),

                DateTimePicker::make('check_out')
                    ->label('Check Out (Jam Pulang)')
                    ->seconds(false)
                    ->native(false)
                    ->displayFormat('d/m/Y H:i')
                    ->helperText('Jam pulang standar: 16:00')
                    ->afterOrEqual('check_in'),

                TextInput::make('fingerprint_id')
                    ->label('Fingerprint ID')
                    ->helperText('ID dari device fingerprint/face recognition')
                    ->maxLength(255),

                Select::make('status')
                    ->label('Status')
                    ->options([
                        'present' => 'Hadir',
                        'late' => 'Terlambat',
                        'half_day' => 'Setengah Hari',
                        'absent' => 'Tidak Hadir',
                        'leave' => 'Cuti',
                        'holiday' => 'Libur',
                    ])
                    ->default('present')
                    ->required(),

                Textarea::make('notes')
                    ->label('Catatan')
                    ->rows(3)
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.name')
                    ->label('Nama')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('attendance_date')
                    ->label('Tanggal')
                    ->date('d/m/Y')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('check_in')
                    ->label('Check In')
                    ->dateTime('H:i')
                    ->sortable()
                    ->badge()
                    ->color(fn ($record) => $record->isLate() ? 'danger' : 'success'),

                TextColumn::make('check_out')
                    ->label('Check Out')
                    ->dateTime('H:i')
                    ->sortable()
                    ->badge()
                    ->color('info'),

                TextColumn::make('work_duration')
                    ->label('Durasi Kerja')
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

                TextColumn::make('status')
                    ->label('Status')
                    ->getStateUsing(fn ($record) => $record->status_label)
                    ->badge()
                    ->color(fn ($record) => $record->status_color),

                TextColumn::make('late_duration')
                    ->label('Keterlambatan')
                    ->getStateUsing(function ($record) {
                        if (!$record->late_duration) {
                            return '-';
                        }
                        return $record->late_duration . ' menit';
                    })
                    ->badge()
                    ->color('warning')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('fingerprint_id')
                    ->label('Fingerprint ID')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('notes')
                    ->label('Catatan')
                    ->limit(30)
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('attendance_date', 'desc')
            ->filters([
                SelectFilter::make('user_id')
                    ->label('User')
                    ->options(User::pluck('name', 'id')->toArray())
                    ->searchable()
                    ->visible(fn () => Auth::user()->hasRole('super_admin')),

                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'present' => 'Hadir',
                        'late' => 'Terlambat',
                        'half_day' => 'Setengah Hari',
                        'absent' => 'Tidak Hadir',
                        'leave' => 'Cuti',
                        'holiday' => 'Libur',
                    ]),

                Filter::make('date_range')
                    ->form([
                        DatePicker::make('from')
                            ->label('Dari Tanggal')
                            ->native(false),
                        DatePicker::make('until')
                            ->label('Sampai Tanggal')
                            ->native(false),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('attendance_date', '>=', $date),
                            )
                            ->when(
                                $data['until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('attendance_date', '<=', $date),
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

                Filter::make('this_month')
                    ->label('Bulan Ini')
                    ->query(fn (Builder $query): Builder => $query->currentMonth())
                    ->toggle(),
            ])
            ->recordActions([
                ViewAction::make(),
                // EditAction disabled - data dari Hikvision tidak boleh diedit manual
                // EditAction::make()
                //     ->visible(fn () => auth()->user()->hasRole('super_admin')),
            ])
            ->toolbarActions([
                // BulkActionGroup disabled - data dari Hikvision tidak boleh dihapus
                // BulkActionGroup::make([
                //     DeleteBulkAction::make()
                //         ->visible(fn () => auth()->user()->hasRole('super_admin')),
                // ]),
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
            'index' => ListAttendances::route('/'),
            // Create disabled - data otomatis dari mesin Hikvision
            // 'create' => CreateAttendance::route('/create'),
            'edit' => EditAttendance::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        if (Auth::user()->hasRole('super_admin')) {
            return (string) Attendance::today()->count();
        }

        return null;
    }
}
