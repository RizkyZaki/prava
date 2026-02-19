<?php

namespace App\Filament\Resources\Projects\Pages;

use Filament\Actions\EditAction;
use Filament\Actions\Action;
use App\Filament\Pages\ProjectBoard;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use App\Filament\Resources\Projects\ProjectResource;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists\Components\TextEntry;
use Filament\Support\Enums\FontWeight;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use App\Models\Disbursement;
use App\Models\Income;
use App\Models\Expense;
use App\Models\Company;
use App\Models\CashAccount;
use App\Models\ExpenseCategory;
use Illuminate\Contracts\View\View;

class ViewProject extends ViewRecord
{
    protected static string $resource = ProjectResource::class;

    protected function getHeaderActions(): array
    {
        $actions = [
            EditAction::make(),
            Action::make('board')
                ->label('Project Board')
                ->icon('heroicon-o-view-columns')
                ->color('info')
                ->url(fn () => ProjectBoard::getUrl(['project_id' => $this->record->id])),
        ];

        // Finance actions
        $user = \Illuminate\Support\Facades\Auth::user();
        if ($user && collect($user->roles)->pluck('name')->contains(fn($role) => in_array($role, ['super_admin', 'finance']))) {
            $actions[] = Action::make('pencairan')
                ->label('Pencairan')
                ->icon('heroicon-o-banknotes')
                ->color('success')
                ->form([
                    Select::make('cash_account_id')
                        ->label('Sumber Dana Tujuan')
                        ->options(function () {
                            if (!$this->record->company_id) return [];
                            return CashAccount::where('company_id', $this->record->company_id)
                                ->where('is_active', true)
                                ->pluck('name', 'id');
                        })
                        ->required()
                        ->searchable()
                        ->helperText('Pilih rekening/kas tujuan pencairan'),
                    TextInput::make('amount')
                        ->label('Nominal Pencairan')
                        ->numeric()
                        ->prefix('Rp')
                        ->required()
                        ->minValue(1)
                        ->maxValue(fn () => $this->record->project_value ?? 99999999999)
                        ->default(fn () => max(0, $this->record->total_expenses - $this->record->total_disbursements))
                        ->helperText(function () {
                            $totalExpenses = $this->record->total_expenses;
                            $totalDisbursements = $this->record->total_disbursements;
                            $sisaBelumCair = max(0, $totalExpenses - $totalDisbursements);
                            $maxNilai = $this->record->project_value ?? 0;
                            return 'Max: Rp ' . number_format($maxNilai, 0, ',', '.') . ' (Nilai Kegiatan) | ' .
                                   'Sisa Belum Dicairkan: Rp ' . number_format($sisaBelumCair, 0, ',', '.');
                        }),
                    DatePicker::make('disbursement_date')
                        ->label('Tanggal Pencairan')
                        ->required()
                        ->default(now())
                        ->native(false)
                        ->displayFormat('d/m/Y'),
                    TextInput::make('description')
                        ->label('Keterangan')
                        ->maxLength(255)
                        ->placeholder('Contoh: Termin 1, Pelunasan, dll'),
                ])
                ->action(function (array $data) {
                    $record = $this->record;

                    Disbursement::create([
                        'project_id' => $record->id,
                        'amount' => $data['amount'],
                        'disbursement_date' => $data['disbursement_date'],
                        'description' => $data['description'] ?? null,
                        'created_by' => \Illuminate\Support\Facades\Auth::id(),
                    ]);

                    if ($record->company_id && isset($data['cash_account_id'])) {
                        Income::create([
                            'company_id' => $record->company_id,
                            'cash_account_id' => $data['cash_account_id'],
                            'project_id' => $record->id,
                            'title' => 'Pencairan: ' . $record->name . ' - ' . ($data['description'] ?? 'Pencairan'),
                            'amount' => $data['amount'],
                                'income_date' => $data['disbursement_date'],
                                'source' => 'project',
                                'status' => 'approved',
                                'created_by' => \Illuminate\Support\Facades\Auth::id(),
                                'approved_by' => \Illuminate\Support\Facades\Auth::id(),
                            ]);

                            $cashAccount = CashAccount::find($data['cash_account_id']);
                            if ($cashAccount) {
                                $cashAccount->recalculateBalance();
                            }
                    }

                    Notification::make()
                        ->title('Pencairan berhasil dicatat')
                        ->body('Rp ' . number_format($data['amount'], 0, ',', '.'))
                        ->success()
                        ->send();
                });

            $actions[] = Action::make('pengeluaran')
                ->label('Pengeluaran')
                ->icon('heroicon-o-arrow-trending-down')
                ->color('danger')
                ->disabled(fn () => $this->record->disbursements()->exists())
                ->tooltip(fn () => $this->record->disbursements()->exists() ? 'Tidak dapat menambah pengeluaran setelah ada pencairan' : null)
                ->form([
                    Select::make('company_id')
                        ->label('Perusahaan')
                        ->options(Company::where('is_active', true)->pluck('name', 'id'))
                        ->required()
                        ->searchable()
                        ->default(fn () => $this->record->company_id)
                        ->live()
                        ->afterStateUpdated(fn ($set) => $set('cash_account_id', null)),
                    Select::make('cash_account_id')
                        ->label('Sumber Dana')
                        ->options(function (\Filament\Schemas\Components\Utilities\Get $get) {
                            $companyId = $get('company_id');
                            if (!$companyId) return [];
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
                    TextInput::make('title')
                        ->label('Judul')
                        ->required()
                        ->maxLength(255),
                    Textarea::make('description')
                        ->label('Keterangan')
                        ->maxLength(1000),
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
                ->action(function (array $data) {
                    Expense::create([
                        'company_id' => $data['company_id'],
                        'cash_account_id' => $data['cash_account_id'],
                        'expense_category_id' => $data['expense_category_id'],
                        'project_id' => $this->record->id,
                        'title' => $data['title'],
                        'description' => $data['description'] ?? null,
                        'amount' => $data['amount'],
                        'expense_date' => $data['expense_date'],
                        'status' => 'pending',
                        'created_by' => \Illuminate\Support\Facades\Auth::id(),
                    ]);

                    Notification::make()
                        ->title('Pengeluaran berhasil dicatat')
                        ->body('Rp ' . number_format($data['amount'], 0, ',', '.'))
                        ->success()
                        ->send();
                });

            $actions[] = Action::make('invoice')
                ->label('Invoice')
                ->icon('heroicon-o-document-text')
                ->color('warning')
                ->modalHeading(fn () => 'Invoice - ' . $this->record->name)
                ->modalContent(function (): View {
                    $record = $this->record;
                    $disbursements = $record->disbursements()->orderBy('disbursement_date', 'desc')->get();

                    return view('filament.modals.project-invoice', [
                        'record' => $record,
                        'disbursements' => $disbursements,
                        'totalDisbursed' => $record->total_disbursements,
                        'totalExpenses' => $record->total_expenses,
                        'projectValue' => $record->project_value ?? 0,
                    ]);
                })
                ->modalSubmitAction(false)
                ->modalCancelActionLabel('Tutup');
        }

        $actions[] = Action::make('external_access')
            ->label('External Dashboard')
            ->icon('heroicon-o-globe-alt')
            ->color('gray')
            ->visible(fn () => ($user = \Illuminate\Support\Facades\Auth::user()) && collect($user->roles)->pluck('name')->contains('super_admin'))
            ->modalHeading('External Dashboard Access')
            ->modalDescription('Share these credentials with external users to access the project dashboard.')
            ->modalContent(function () {
                    $record = $this->record;
                    $externalAccess = $record->externalAccess;

                    if (!$externalAccess) {
                        $externalAccess = $record->generateExternalAccess();
                    }

                    $dashboardUrl = url('/external/' . $externalAccess->access_token);

                    return view('filament.components.external-access-modal', [
                        'dashboardUrl' => $dashboardUrl,
                        'password' => $externalAccess->password,
                        'lastAccessed' => $externalAccess->last_accessed_at ? $externalAccess->last_accessed_at->format('d/m/Y H:i') : null,
                        'isActive' => $externalAccess->is_active,
                    ]);
                })
                ->modalSubmitAction(false)
                ->modalCancelActionLabel('Close');

        return $actions;
    }

    public function infolist(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Tabs::make('project_tabs')
                    ->tabs([
                        Tab::make('Informasi')
                            ->icon('heroicon-o-information-circle')
                            ->schema([
                                Section::make('Project Information')
                                    ->schema([
                                        Grid::make(2)
                                            ->schema([
                                                TextEntry::make('name')
                                                    ->label('Project Name')
                                                    ->weight(FontWeight::Bold)
                                                    ->size('lg'),
                                                TextEntry::make('ticket_prefix')
                                                    ->label('Ticket Prefix')
                                                    ->badge()
                                                    ->color('primary'),
                                            ]),
                                        Grid::make(4)
                                            ->schema([
                                                TextEntry::make('company.name')
                                                    ->label('Perusahaan (PT)')
                                                    ->badge()
                                                    ->color('info')
                                                    ->placeholder('Belum diatur'),
                                                TextEntry::make('region.name')
                                                    ->label('Wilayah / Kota')
                                                    ->placeholder('Belum diatur'),
                                                TextEntry::make('institution.name')
                                                    ->label('Instansi')
                                                    ->placeholder('Belum diatur'),
                                                TextEntry::make('subInstitution.name')
                                                    ->label('Sub Instansi')
                                                    ->placeholder('Belum diatur'),
                                            ]),
                                        TextEntry::make('description')
                                            ->label('Description')
                                            ->html()
                                            ->columnSpanFull(),
                                        Grid::make(2)
                                            ->schema([
                                                TextEntry::make('start_date')
                                                    ->label('Start Date')
                                                    ->date('d/m/Y')
                                                    ->placeholder('Not set'),
                                                TextEntry::make('end_date')
                                                    ->label('End Date')
                                                    ->date('d/m/Y')
                                                    ->placeholder('Not set'),
                                            ]),
                                        Grid::make(2)
                                            ->schema([
                                                TextEntry::make('remaining_days')
                                                    ->label('Remaining Days')
                                                    ->getStateUsing(function ($record): ?string {
                                                        if (!$record->end_date) {
                                                            return 'Not set';
                                                        }
                                                        return $record->remaining_days . ' days';
                                                    })
                                                    ->badge()
                                                    ->color(fn ($record): string =>
                                                        !$record->end_date ? 'gray' :
                                                        ($record->remaining_days <= 0 ? 'danger' :
                                                        ($record->remaining_days <= 7 ? 'warning' : 'success'))
                                                    ),
                                                TextEntry::make('pinned_date')
                                                    ->label('Pinned Status')
                                                    ->getStateUsing(function ($record): string {
                                                        return $record->pinned_date ? 'Pinned on ' . $record->pinned_date->format('d/m/Y H:i') : 'Not pinned';
                                                    })
                                                    ->badge()
                                                    ->color(fn ($record): string => $record->pinned_date ? 'success' : 'gray'),
                                            ]),
                                    ]),

                                Section::make('Project Statistics')
                                    ->schema([
                                        Grid::make(4)
                                            ->schema([
                                                TextEntry::make('members_count')
                                                    ->label('Total Members')
                                                    ->getStateUsing(fn ($record) => $record->members()->count())
                                                    ->badge()
                                                    ->color('info'),
                                                TextEntry::make('member_names')
                                                    ->label('Member Names')
                                                    ->getStateUsing(fn ($record) => $record->members->pluck('name')->implode(', '))
                                                    ->html()
                                                    ->columnSpanFull(),
                                                TextEntry::make('tickets_count')
                                                    ->label('Total Tickets')
                                                    ->getStateUsing(fn ($record) => $record->tickets()->count())
                                                    ->badge()
                                                    ->color('primary'),
                                                TextEntry::make('epics_count')
                                                    ->label('Total Epics')
                                                    ->getStateUsing(fn ($record) => $record->epics()->count())
                                                    ->badge()
                                                    ->color('warning'),
                                                TextEntry::make('statuses_count')
                                                    ->label('Ticket Statuses')
                                                    ->getStateUsing(fn ($record) => $record->ticketStatuses()->count())
                                                    ->badge()
                                                    ->color('success'),
                                            ]),
                                    ]),

                                Section::make('Timestamps')
                                    ->schema([
                                        Grid::make(2)
                                            ->schema([
                                                TextEntry::make('created_at')
                                                    ->label('Created At')
                                                    ->dateTime('d/m/Y H:i'),
                                                TextEntry::make('updated_at')
                                                    ->label('Last Updated')
                                                    ->dateTime('d/m/Y H:i'),
                                            ]),
                                    ])
                                    ->collapsible(),
                            ]),

                        Tab::make('Keuangan')
                            ->icon('heroicon-o-banknotes')
                            ->visible(fn () => ($user = \Illuminate\Support\Facades\Auth::user()) && collect($user->roles)->pluck('name')->contains(fn($role) => in_array($role, ['super_admin', 'finance'])))
                            ->schema([
                                \Filament\Infolists\Components\ViewEntry::make('finance_section')
                                    ->view('filament.components.project-finance-section')
                                    ->columnSpanFull(),
                            ]),

                        Tab::make('Breakdown Items')
                            ->icon('heroicon-o-clipboard-document-list')
                            ->visible(fn () => $this->record->projectItems()->exists())
                            ->badge(fn () => $this->record->projectItems()->count())
                            ->schema([
                                Section::make('Item Kegiatan/Pengadaan')
                                    ->description('Detail barang/jasa yang termasuk dalam project ini')
                                    ->schema([
                                        \Filament\Infolists\Components\ViewEntry::make('items_section')
                                            ->view('filament.components.project-items-section')
                                            ->columnSpanFull(),
                                    ]),
                            ]),
                    ])
                    ->columnSpanFull(),
            ]);
    }
}
