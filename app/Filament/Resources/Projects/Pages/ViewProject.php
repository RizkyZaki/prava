<?php

namespace App\Filament\Resources\Projects\Pages;

use Filament\Actions\EditAction;
use Filament\Actions\Action;
use App\Filament\Pages\ProjectBoard;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Grid;
use App\Filament\Resources\Projects\ProjectResource;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists\Components\TextEntry;
use Filament\Support\Enums\FontWeight;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Filament\Notifications\Notification;
use App\Models\Disbursement;
use App\Models\Income;
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
        if (auth()->user()->hasRole(['super_admin', 'finance'])) {
            $actions[] = Action::make('pencairan')
                ->label('Pencairan')
                ->icon('heroicon-o-banknotes')
                ->color('success')
                ->form([
                    TextInput::make('amount')
                        ->label('Nominal Pencairan')
                        ->numeric()
                        ->prefix('Rp')
                        ->required()
                        ->minValue(1),
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
                        'created_by' => auth()->id(),
                    ]);

                    if ($record->company_id) {
                        $cashAccount = \App\Models\CashAccount::where('company_id', $record->company_id)
                            ->where('is_active', true)
                            ->first();

                        if ($cashAccount) {
                            Income::create([
                                'company_id' => $record->company_id,
                                'cash_account_id' => $cashAccount->id,
                                'project_id' => $record->id,
                                'title' => 'Pencairan: ' . $record->name . ' - ' . ($data['description'] ?? 'Pencairan'),
                                'amount' => $data['amount'],
                                'income_date' => $data['disbursement_date'],
                                'source' => 'project',
                                'status' => 'approved',
                                'created_by' => auth()->id(),
                                'approved_by' => auth()->id(),
                            ]);

                            $cashAccount->recalculateBalance();
                        }
                    }

                    Notification::make()
                        ->title('Pencairan berhasil dicatat')
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
            ->visible(fn () => auth()->user()->hasRole('super_admin'))
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
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('project_value')
                                    ->label('Nilai Kegiatan / Kontrak')
                                    ->money('IDR')
                                    ->placeholder('Belum diatur'),
                                TextEntry::make('total_disbursements')
                                    ->label('Total Pencairan')
                                    ->money('IDR')
                                    ->getStateUsing(fn ($record) => $record->total_disbursements)
                                    ->badge()
                                    ->color('success'),
                                TextEntry::make('total_expenses')
                                    ->label('Total Pengeluaran')
                                    ->money('IDR')
                                    ->getStateUsing(fn ($record) => $record->total_expenses)
                                    ->badge()
                                    ->color(fn ($record) => $record->project_value && $record->total_expenses > $record->project_value ? 'danger' : 'warning'),
                            ])
                            ->visible(fn () => auth()->user()->hasRole(['super_admin', 'finance'])),
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

                Section::make('Keuangan Project')
                    ->icon('heroicon-o-chart-bar')
                    ->visible(fn () => auth()->user()->hasRole(['super_admin', 'finance']))
                    ->schema([
                        \Filament\Infolists\Components\ViewEntry::make('finance_section')
                            ->view('filament.components.project-finance-section')
                            ->columnSpanFull(),
                    ])
                    ->collapsible(),

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
            ]);
    }
}
