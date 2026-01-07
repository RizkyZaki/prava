<?php

namespace App\Filament\Resources\WorkSchedules;

use App\Filament\Resources\WorkSchedules\Pages\CreateWorkSchedule;
use App\Filament\Resources\WorkSchedules\Pages\EditWorkSchedule;
use App\Filament\Resources\WorkSchedules\Pages\ListWorkSchedules;
use App\Models\WorkSchedule;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section as ComponentsSection;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class WorkScheduleResource extends Resource
{
    protected static ?string $model = WorkSchedule::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-clock';

    protected static ?string $navigationLabel = 'Work Schedules';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                ComponentsSection::make('Schedule Information')
                    ->description('Basic schedule information')
                    ->schema([
                        TextInput::make('name')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('e.g., "Default Schedule", "Shift Pagi"'),

                        Textarea::make('description')
                            ->rows(2)
                            ->maxLength(500),

                        Checkbox::make('is_default')
                            ->label('Default Schedule')
                            ->helperText('Only one schedule can be default'),

                        Checkbox::make('is_active')
                            ->label('Active')
                            ->default(true),
                    ])
                    ->columns(2),

                ComponentsSection::make('Work Hours')
                    ->description('Define work hours and break times')
                    ->schema([
                        TimePicker::make('start_time')
                            ->label('Start Time (Jam Masuk)')
                            ->required()
                            ->seconds(false)
                            ->default('08:00'),

                        TimePicker::make('end_time')
                            ->label('End Time (Jam Pulang)')
                            ->required()
                            ->seconds(false)
                            ->default('16:00'),

                        TimePicker::make('break_start')
                            ->label('Break Start (Jam Istirahat Mulai)')
                            ->seconds(false)
                            ->default('12:00'),

                        TimePicker::make('break_end')
                            ->label('Break End (Jam Istirahat Selesai)')
                            ->seconds(false)
                            ->default('13:00'),

                        TextInput::make('daily_work_hours')
                            ->label('Daily Work Hours')
                            ->numeric()
                            ->default(8)
                            ->suffix('hours')
                            ->required(),
                    ])
                    ->columns(2),

                ComponentsSection::make('Tolerance & Deductions')
                    ->description('Configure tolerances and deduction rates')
                    ->schema([
                        TextInput::make('late_tolerance_minutes')
                            ->label('Late Tolerance')
                            ->numeric()
                            ->default(15)
                            ->suffix('minutes')
                            ->required()
                            ->helperText('Allowed late time without deduction'),

                        TextInput::make('early_leave_tolerance_minutes')
                            ->label('Early Leave Tolerance')
                            ->numeric()
                            ->default(15)
                            ->suffix('minutes')
                            ->required()
                            ->helperText('Allowed early leave without deduction'),

                        TextInput::make('hourly_deduction_rate')
                            ->label('Hourly Deduction Rate')
                            ->numeric()
                            ->default(0)
                            ->suffix('% of daily salary')
                            ->helperText('Deduction percentage per hour late/early'),
                    ])
                    ->columns(3),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('start_time')
                    ->label('Work Hours')
                    ->formatStateUsing(fn (WorkSchedule $record) =>
                        $record->start_time->format('H:i') . ' - ' . $record->end_time->format('H:i')
                    ),

                TextColumn::make('break_start')
                    ->label('Break Time')
                    ->formatStateUsing(fn (WorkSchedule $record) =>
                        $record->break_start && $record->break_end
                            ? $record->break_start->format('H:i') . ' - ' . $record->break_end->format('H:i')
                            : '-'
                    ),

                TextColumn::make('daily_work_hours')
                    ->label('Daily Hours')
                    ->suffix(' hrs')
                    ->alignCenter(),

                TextColumn::make('late_tolerance_minutes')
                    ->label('Late Tolerance')
                    ->suffix(' min')
                    ->alignCenter(),

                IconColumn::make('is_default')
                    ->label('Default')
                    ->boolean()
                    ->alignCenter(),

                IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean()
                    ->alignCenter(),
            ])
            ->defaultSort('is_default', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => ListWorkSchedules::route('/'),
            'create' => CreateWorkSchedule::route('/create'),
            'edit' => EditWorkSchedule::route('/{record}/edit'),
        ];
    }
}
