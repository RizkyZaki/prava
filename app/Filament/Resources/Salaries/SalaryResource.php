<?php

namespace App\Filament\Resources\Salaries;

use App\Filament\Resources\Salaries\Pages\CreateSalary;
use App\Filament\Resources\Salaries\Pages\EditSalary;
use App\Filament\Resources\Salaries\Pages\ListSalaries;
use App\Models\Salary;
use App\Models\User;
use App\Models\WorkSchedule;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Placeholder;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section as ComponentsSection;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Tables\Filters\SelectFilter;

class SalaryResource extends Resource
{
    protected static ?string $model = Salary::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-banknotes';

    protected static ?string $navigationLabel = 'Salaries';

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                ComponentsSection::make('Employee Information')
                    ->schema([
                        Select::make('user_id')
                            ->label('Employee')
                            ->relationship('user', 'name')
                            ->searchable()
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->helperText('Select employee for this salary configuration'),

                        Select::make('work_schedule_id')
                            ->label('Work Schedule')
                            ->options(WorkSchedule::where('is_active', true)->pluck('name', 'id'))
                            ->searchable()
                            ->helperText('Work schedule for this employee (optional)'),
                    ])
                    ->columns(2),

                ComponentsSection::make('Salary Components')
                    ->description('Configure salary and allowances')
                    ->schema([
                        TextInput::make('base_salary')
                            ->label('Base Salary (Gaji Pokok)')
                            ->numeric()
                            ->required()
                            ->prefix('Rp')
                            ->helperText('Monthly base salary'),

                        TextInput::make('transport_allowance')
                            ->label('Transport Allowance')
                            ->numeric()
                            ->default(0)
                            ->prefix('Rp'),

                        TextInput::make('meal_allowance')
                            ->label('Meal Allowance')
                            ->numeric()
                            ->default(0)
                            ->prefix('Rp'),

                        TextInput::make('position_allowance')
                            ->label('Position Allowance')
                            ->numeric()
                            ->default(0)
                            ->prefix('Rp'),

                        TextInput::make('other_allowance')
                            ->label('Other Allowance')
                            ->numeric()
                            ->default(0)
                            ->prefix('Rp'),

                        Placeholder::make('gross_salary_preview')
                            ->label('Gross Salary (Preview)')
                            ->content(function ($get) {
                                $total = ($get('base_salary') ?? 0) +
                                        ($get('transport_allowance') ?? 0) +
                                        ($get('meal_allowance') ?? 0) +
                                        ($get('position_allowance') ?? 0) +
                                        ($get('other_allowance') ?? 0);
                                return 'Rp ' . number_format($total, 0, ',', '.');
                            }),
                    ])
                    ->columns(3),

                ComponentsSection::make('Deduction Rules')
                    ->description('Enable/disable automatic deductions')
                    ->schema([
                        Checkbox::make('enable_late_deduction')
                            ->label('Enable Late Deduction')
                            ->default(true)
                            ->helperText('Deduct salary for late arrival'),

                        Checkbox::make('enable_early_leave_deduction')
                            ->label('Enable Early Leave Deduction')
                            ->default(true)
                            ->helperText('Deduct salary for early leave'),

                        Checkbox::make('enable_absent_deduction')
                            ->label('Enable Absent Deduction')
                            ->default(true)
                            ->helperText('Deduct salary for absence'),
                    ])
                    ->columns(3),

                ComponentsSection::make('Effective Period')
                    ->description('When this salary configuration is active')
                    ->schema([
                        DatePicker::make('effective_from')
                            ->label('Effective From')
                            ->required()
                            ->default(now()),

                        DatePicker::make('effective_to')
                            ->label('Effective To')
                            ->helperText('Leave empty for ongoing'),

                        Checkbox::make('is_active')
                            ->label('Active')
                            ->default(true),
                    ])
                    ->columns(3),

                ComponentsSection::make('Additional Notes')
                    ->schema([
                        Textarea::make('notes')
                            ->rows(2)
                            ->maxLength(500),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.name')
                    ->label('Employee')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('base_salary')
                    ->label('Base Salary')
                    ->money('IDR')
                    ->sortable(),

                TextColumn::make('gross_salary')
                    ->label('Gross Salary')
                    ->money('IDR')
                    ->sortable(),

                TextColumn::make('workSchedule.name')
                    ->label('Schedule')
                    ->default('-'),

                TextColumn::make('effective_from')
                    ->label('From')
                    ->date('d M Y')
                    ->sortable(),

                TextColumn::make('effective_to')
                    ->label('To')
                    ->date('d M Y')
                    ->placeholder('-'),

                IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean()
                    ->alignCenter(),
            ])
            ->filters([
                SelectFilter::make('user_id')
                    ->label('Employee')
                    ->relationship('user', 'name')
                    ->searchable(),

                SelectFilter::make('is_active')
                    ->label('Status')
                    ->options([
                        '1' => 'Active',
                        '0' => 'Inactive',
                    ]),
            ])
            ->defaultSort('effective_from', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSalaries::route('/'),
            'create' => CreateSalary::route('/create'),
            'edit' => EditSalary::route('/{record}/edit'),
        ];
    }
}
