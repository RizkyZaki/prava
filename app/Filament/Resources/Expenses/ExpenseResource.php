<?php

namespace App\Filament\Resources\Expenses;

use App\Filament\Resources\Expenses\Pages\CreateExpense;
use App\Filament\Resources\Expenses\Pages\EditExpense;
use App\Filament\Resources\Expenses\Pages\ListExpenses;
use App\Models\CashAccount;
use App\Models\Company;
use App\Models\Expense;
use App\Models\ExpenseCategory;
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
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ExpenseResource extends Resource
{
    protected static ?string $model = Expense::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-receipt-percent';

    protected static ?string $navigationLabel = 'Pengeluaran';

    protected static string|\UnitEnum|null $navigationGroup = 'Keuangan';

    protected static ?int $navigationSort = 4;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Detail Pengeluaran')
                    ->schema([
                        Select::make('company_id')
                            ->label('Perusahaan')
                            ->options(Company::where('is_active', true)->pluck('name', 'id'))
                            ->required()
                            ->searchable()
                            ->live()
                            ->afterStateUpdated(fn ($set) => $set('cash_account_id', null)),

                        Select::make('cash_account_id')
                            ->label('Kas')
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

                        Select::make('expense_category_id')
                            ->label('Kategori')
                            ->options(ExpenseCategory::where('is_active', true)->pluck('name', 'id'))
                            ->required()
                            ->searchable(),

                        Select::make('project_id')
                            ->label('Project')
                            ->relationship('project', 'name')
                            ->searchable()
                            ->preload()
                            ->nullable()
                            ->helperText('Opsional. Pilih project terkait pengeluaran ini.'),

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
                            ->numeric()
                            ->prefix('Rp')
                            ->required()
                            ->minValue(1),

                        DatePicker::make('expense_date')
                            ->label('Tanggal Pengeluaran')
                            ->required()
                            ->default(now())
                            ->native(false)
                            ->displayFormat('d/m/Y'),
                    ])
                    ->columns(2),

                Section::make('Bukti Pengeluaran')
                    ->schema([
                        FileUpload::make('receipt')
                            ->label('Bukti/Kwitansi')
                            ->directory('expense-receipts')
                            ->disk('public')
                            ->acceptedFileTypes(['image/*', 'application/pdf'])
                            ->maxSize(5120)
                            ->helperText('Upload foto/scan bukti pengeluaran (maks 5MB)'),
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
                TextColumn::make('expense_date')
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
                    ->label('Kas')
                    ->searchable(),

                TextColumn::make('category.name')
                    ->label('Kategori')
                    ->badge()
                    ->color('warning')
                    ->searchable(),

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
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('company_id')
                    ->label('Perusahaan')
                    ->relationship('company', 'name'),

                SelectFilter::make('expense_category_id')
                    ->label('Kategori')
                    ->relationship('category', 'name'),

                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'pending' => 'Pending',
                        'approved' => 'Disetujui',
                        'rejected' => 'Ditolak',
                    ]),

                Filter::make('expense_date')
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
                                fn (Builder $query, $date): Builder => $query->whereDate('expense_date', '>=', $date),
                            )
                            ->when(
                                $data['until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('expense_date', '<=', $date),
                            );
                    }),
            ])
            ->actions([
                ViewAction::make(),
                EditAction::make()
                    ->visible(fn (Expense $record) => $record->status === 'pending'),

                Action::make('approve')
                    ->label('Setujui')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Setujui Pengeluaran')
                    ->modalDescription('Apakah Anda yakin ingin menyetujui pengeluaran ini? Saldo kas akan berkurang.')
                    ->visible(fn (Expense $record) => $record->status === 'pending' && auth()->user()->hasRole(['super_admin', 'finance']))
                    ->action(function (Expense $record) {
                        $record->update([
                            'status' => 'approved',
                            'approved_by' => auth()->id(),
                        ]);

                        $record->cashAccount->recalculateBalance();

                        Notification::make()
                            ->title('Pengeluaran disetujui')
                            ->success()
                            ->send();
                    }),

                Action::make('reject')
                    ->label('Tolak')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('Tolak Pengeluaran')
                    ->form([
                        Textarea::make('rejection_reason')
                            ->label('Alasan Penolakan')
                            ->required(),
                    ])
                    ->visible(fn (Expense $record) => $record->status === 'pending' && auth()->user()->hasRole(['super_admin', 'finance']))
                    ->action(function (Expense $record, array $data) {
                        $record->update([
                            'status' => 'rejected',
                            'approved_by' => auth()->id(),
                            'rejection_reason' => $data['rejection_reason'],
                        ]);

                        Notification::make()
                            ->title('Pengeluaran ditolak')
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
            'index' => ListExpenses::route('/'),
            'create' => CreateExpense::route('/create'),
            'edit' => EditExpense::route('/{record}/edit'),
        ];
    }
}
