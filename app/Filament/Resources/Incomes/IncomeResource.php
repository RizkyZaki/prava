<?php

namespace App\Filament\Resources\Incomes;

use App\Filament\Resources\Incomes\Pages\CreateIncome;
use App\Filament\Resources\Incomes\Pages\EditIncome;
use App\Filament\Resources\Incomes\Pages\ListIncomes;
use App\Models\CashAccount;
use App\Models\Company;
use App\Models\Income;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class IncomeResource extends Resource
{
    protected static ?string $model = Income::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-arrow-trending-up';

    protected static ?string $navigationLabel = 'Pemasukan';

    protected static string|\UnitEnum|null $navigationGroup = 'Keuangan';

    protected static ?int $navigationSort = 5;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Detail Pemasukan')
                    ->schema([
                        Select::make('company_id')
                            ->label('Perusahaan')
                            ->options(Company::where('is_active', true)->pluck('name', 'id'))
                            ->required()
                            ->searchable()
                            ->live()
                            ->afterStateUpdated(fn ($set) => $set('cash_account_id', null)),

                        Select::make('cash_account_id')
                            ->label('Sumber Dana')
                            ->options(function (Get $get) {
                                $companyId = $get('company_id');
                                if (!$companyId) {
                                    return [];
                                }
                                return CashAccount::where('company_id', $companyId)
                                    ->where('is_active', true)
                                    ->pluck('name', 'id');
                            })
                            ->required()
                            ->searchable(),

                        Select::make('project_id')
                            ->label('Project')
                            ->relationship('project', 'name')
                            ->searchable()
                            ->preload()
                            ->nullable()
                            ->helperText('Opsional. Pilih project terkait pemasukan ini.'),

                        Select::make('source')
                            ->label('Sumber Pemasukan')
                            ->options([
                                'project' => 'Pencairan Project',
                                'jasa' => 'Jasa / Konsultasi',
                                'penjualan' => 'Penjualan',
                                'lainnya' => 'Lainnya',
                            ])
                            ->required()
                            ->searchable(),

                        TextInput::make('title')
                            ->label('Judul')
                            ->required()
                            ->maxLength(255),

                        Textarea::make('description')
                            ->label('Keterangan')
                            ->maxLength(1000)
                            ->columnSpanFull(),

                        TextInput::make('amount')
                            ->label('Jumlah')
                            ->prefix('Rp')
                            ->required()
                            ->minLength(1),

                        DatePicker::make('income_date')
                            ->label('Tanggal Pemasukan')
                            ->required()
                            ->default(now())
                            ->native(false)
                            ->displayFormat('d/m/Y'),
                    ])
                    ->columns(2),

                Section::make('Bukti Pemasukan')
                    ->schema([
                        FileUpload::make('receipt')
                            ->label('Bukti/Kwitansi')
                            ->directory('income-receipts')
                            ->disk('public')
                            ->acceptedFileTypes(['image/*', 'application/pdf'])
                            ->maxSize(5120)
                            ->helperText('Upload foto/scan bukti pemasukan (maks 5MB)'),
                    ]),

                Hidden::make('created_by')
                    ->default(fn () => auth()->id()),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('income_date')
                    ->label('Tanggal')
                    ->date('d M Y')
                    ->sortable(),

                TextColumn::make('company.name')
                    ->label('Perusahaan')
                    ->badge()
                    ->color('primary')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('cashAccount.name')
                    ->label('Sumber Dana')
                    ->searchable(),

                TextColumn::make('source')
                    ->label('Sumber')
                    ->badge()
                    ->color(fn (?string $state): string => match ($state) {
                        'project' => 'info',
                        'jasa' => 'success',
                        'penjualan' => 'warning',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        'project' => 'Project',
                        'jasa' => 'Jasa',
                        'penjualan' => 'Penjualan',
                        'lainnya' => 'Lainnya',
                        default => '-',
                    }),

                TextColumn::make('project.name')
                    ->label('Project')
                    ->searchable()
                    ->sortable()
                    ->placeholder('—')
                    ->toggleable(),

                TextColumn::make('title')
                    ->label('Judul')
                    ->searchable()
                    ->limit(40),

                TextColumn::make('amount')
                    ->label('Jumlah')
                    ->money('IDR')
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('creator.name')
                    ->label('Dibuat Oleh')
                    ->searchable()
                    ->toggleable(),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'approved' => 'success',
                        'rejected' => 'danger',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'pending' => 'Pending',
                        'approved' => 'Disetujui',
                        'rejected' => 'Ditolak',
                    }),

                TextColumn::make('approver.name')
                    ->label('Disetujui Oleh')
                    ->placeholder('—')
                    ->toggleable(),
            ])
            ->filters([
                SelectFilter::make('company_id')
                    ->label('Perusahaan')
                    ->relationship('company', 'name'),

                SelectFilter::make('project_id')
                    ->label('Project')
                    ->relationship('project', 'name')
                    ->searchable()
                    ->preload(),

                SelectFilter::make('source')
                    ->label('Sumber')
                    ->options([
                        'project' => 'Pencairan Project',
                        'jasa' => 'Jasa / Konsultasi',
                        'penjualan' => 'Penjualan',
                        'lainnya' => 'Lainnya',
                    ]),

                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'pending' => 'Pending',
                        'approved' => 'Disetujui',
                        'rejected' => 'Ditolak',
                    ]),

                Filter::make('income_date')
                    ->form([
                        DatePicker::make('from')
                            ->label('Dari Tanggal'),
                        DatePicker::make('until')
                            ->label('Sampai Tanggal'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('income_date', '>=', $date),
                            )
                            ->when(
                                $data['until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('income_date', '<=', $date),
                            );
                    }),
            ])
            ->actions([
                ViewAction::make(),
                EditAction::make()
                    ->visible(fn (Income $record) => $record->status === 'pending'),

                Action::make('approve')
                    ->label('Setujui')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Setujui Pemasukan')
                    ->modalDescription('Apakah Anda yakin ingin menyetujui pemasukan ini? Saldo sumber dana akan bertambah.')
                    ->visible(fn (Income $record) => $record->status === 'pending' && auth()->user()->hasRole(['super_admin', 'finance']))
                    ->action(function (Income $record) {
                        $record->update([
                            'status' => 'approved',
                            'approved_by' => auth()->id(),
                        ]);

                        $record->cashAccount->recalculateBalance();

                        Notification::make()
                            ->title('Pemasukan disetujui')
                            ->success()
                            ->send();
                    }),

                Action::make('reject')
                    ->label('Tolak')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('Tolak Pemasukan')
                    ->form([
                        Textarea::make('rejection_reason')
                            ->label('Alasan Penolakan')
                            ->required(),
                    ])
                    ->visible(fn (Income $record) => $record->status === 'pending' && auth()->user()->hasRole(['super_admin', 'finance']))
                    ->action(function (Income $record, array $data) {
                        $record->update([
                            'status' => 'rejected',
                            'approved_by' => auth()->id(),
                            'rejection_reason' => $data['rejection_reason'],
                        ]);

                        Notification::make()
                            ->title('Pemasukan ditolak')
                            ->danger()
                            ->send();
                    }),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListIncomes::route('/'),
            'create' => CreateIncome::route('/create'),
            'edit' => EditIncome::route('/{record}/edit'),
        ];
    }
}
