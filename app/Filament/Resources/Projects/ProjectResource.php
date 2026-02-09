<?php

namespace App\Filament\Resources\Projects;

use Filament\Schemas\Schema;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\ColorPicker;
use App\Filament\Resources\Projects\Pages\CreateProject;
use Filament\Forms\Components\DateTimePicker;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Columns\ColorColumn;
use Filament\Actions\ViewAction;
use Filament\Actions\EditAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use App\Filament\Resources\Projects\RelationManagers\TicketStatusesRelationManager;
use App\Filament\Resources\Projects\RelationManagers\MembersRelationManager;
use App\Filament\Resources\Projects\RelationManagers\EpicsRelationManager;
use App\Filament\Resources\Projects\RelationManagers\TicketsRelationManager;
use App\Filament\Resources\Projects\RelationManagers\NotesRelationManager;
use App\Filament\Resources\Projects\Pages\ListProjects;
use App\Filament\Resources\Projects\Pages\ViewProject;
use App\Filament\Resources\Projects\Pages\EditProject;
use App\Filament\Actions\ImportTicketsAction;
use App\Filament\Resources\ProjectResource\Pages;
use App\Filament\Resources\ProjectResource\RelationManagers;
use App\Models\Project;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ProjectResource extends Resource
{
    protected static ?string $model = Project::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static string | \UnitEnum | null $navigationGroup = 'Project Management';
    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                RichEditor::make('description')
                    ->columnSpanFull(),
                TextInput::make('ticket_prefix')
                    ->required()
                    ->maxLength(255),
                ColorPicker::make('color')
                    ->label('Project Color')
                    ->helperText('Choose a color for the project card and badge')
                    ->nullable(),
                DatePicker::make('start_date')
                    ->label('Start Date')
                    ->native(false)
                    ->displayFormat('d/m/Y'),
                DatePicker::make('end_date')
                    ->label('End Date')
                    ->native(false)
                    ->displayFormat('d/m/Y')
                    ->afterOrEqual('start_date'),
                Toggle::make('create_default_statuses')
                    ->label('Use Default Ticket Statuses')
                    ->helperText('Create standard Backlog, To Do, In Progress, Review, and Done statuses automatically')
                    ->default(true)
                    ->dehydrated(false)
                    ->visible(fn ($livewire) => $livewire instanceof CreateProject),

                Toggle::make('is_pinned')
                    ->label('Pin Project')
                    ->helperText('Pinned projects will appear in the dashboard timeline')
                    ->live()
                    ->afterStateUpdated(function ($state, $set) {
                        if ($state) {
                            $set('pinned_date', now());
                        } else {
                            $set('pinned_date', null);
                        }
                    })
                    ->dehydrated(false)
                    ->afterStateHydrated(function ($component, $state, $get) {
                        $component->state(!is_null($get('pinned_date')));
                    }),
                DateTimePicker::make('pinned_date')
                    ->label('Pinned Date')
                    ->native(false)
                    ->displayFormat('d/m/Y H:i')
                    ->visible(fn ($get) => $get('is_pinned'))
                    ->dehydrated(true),

                Forms\Components\Select::make('status')
                    ->label('Project Status')
                    ->options([
                        'active' => 'Active',
                        'completed' => 'Completed',
                        'on_hold' => 'On Hold',
                        'cancelled' => 'Cancelled',
                    ])
                    ->default('active')
                    ->required()
                    ->live()
                    ->afterStateUpdated(function ($state, $set) {
                        if ($state === 'completed') {
                            $set('completed_at', now());
                        } else {
                            $set('completed_at', null);
                        }
                    }),

                DateTimePicker::make('completed_at')
                    ->label('Completed At')
                    ->native(false)
                    ->displayFormat('d/m/Y H:i')
                    ->visible(fn ($get) => $get('status') === 'completed')
                    ->dehydrated(true),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ColorColumn::make('color')
                    ->label('')
                    ->width('40px')
                    ->default('#6B7280'),
                TextColumn::make('name')
                    ->searchable(),
                TextColumn::make('ticket_prefix')
                    ->searchable(),
                TextColumn::make('progress_percentage')
                    ->label('Progress')
                    ->getStateUsing(function (Project $record): string {
                        return $record->progress_percentage . '%';
                    })
                    ->badge()
                    ->color(fn (Project $record): string =>
                        $record->progress_percentage >= 100 ? 'success' :
                        ($record->progress_percentage >= 75 ? 'info' :
                        ($record->progress_percentage >= 50 ? 'warning' :
                        ($record->progress_percentage >= 25 ? 'gray' : 'danger')))
                    )
                    ->sortable(),
                TextColumn::make('start_date')
                    ->date('d/m/Y')
                    ->sortable(),
                TextColumn::make('end_date')
                    ->date('d/m/Y')
                    ->sortable(),
                TextColumn::make('remaining_days')
                    ->label('Remaining Days')
                    ->getStateUsing(function (Project $record): ?string {
                        if (!$record->end_date) {
                            return null;
                        }

                        return $record->remaining_days . ' days';
                    })
                    ->badge()
                    ->color(fn (Project $record): string =>
                        !$record->end_date ? 'gray' :
                        ($record->remaining_days <= 0 ? 'danger' :
                        ($record->remaining_days <= 7 ? 'warning' : 'success'))
                    ),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'active' => 'success',
                        'completed' => 'info',
                        'on_hold' => 'warning',
                        'cancelled' => 'danger',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'active' => 'Active',
                        'completed' => 'Completed',
                        'on_hold' => 'On Hold',
                        'cancelled' => 'Cancelled',
                    })
                    ->sortable(),
                TextColumn::make('completed_at')
                    ->label('Completed')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                ToggleColumn::make('is_pinned')
                    ->label('Pinned')
                    ->updateStateUsing(function ($record, $state) {
                        // Gunakan method pin/unpin yang sudah ada di model
                        if ($state) {
                            $record->pin();
                        } else {
                            $record->unpin();
                        }
                        return $state;
                    }),
                TextColumn::make('members_count')
                    ->counts('members')
                    ->label('Members'),
                TextColumn::make('tickets_count')
                    ->counts('tickets')
                    ->label('Tickets'),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                Action::make('mark_completed')
                    ->label('Mark as Completed')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalDescription('All tickets in this project will be moved to "Done" status. Are you sure?')
                    ->visible(fn (Project $record): bool => $record->status !== 'completed')
                    ->action(function (Project $record) {
                        // Cari status "done/completed" di project ini
                        $completedStatus = $record->ticketStatuses()
                            ->where('is_completed', true)
                            ->first();

                        if ($completedStatus) {
                            // Pindahkan semua ticket yang belum done ke status completed
                            $record->tickets()
                                ->where('ticket_status_id', '!=', $completedStatus->id)
                                ->update(['ticket_status_id' => $completedStatus->id]);
                        }

                        $record->update([
                            'status' => 'completed',
                            'completed_at' => now(),
                        ]);

                        Notification::make()
                            ->title('Project marked as completed')
                            ->body('All tickets have been moved to Done.')
                            ->success()
                            ->send();
                    }),
                Action::make('reopen')
                    ->label('Reopen Project')
                    ->icon('heroicon-o-arrow-path')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->visible(fn (Project $record): bool => $record->status === 'completed')
                    ->action(function (Project $record) {
                        $record->update([
                            'status' => 'active',
                            'completed_at' => null,
                        ]);

                        Notification::make()
                            ->title('Project reopened')
                            ->success()
                            ->send();
                    }),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'active' => 'Active',
                        'completed' => 'Completed',
                        'on_hold' => 'On Hold',
                        'cancelled' => 'Cancelled',
                    ])
                    ->multiple(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            TicketStatusesRelationManager::class,
            MembersRelationManager::class,
            EpicsRelationManager::class,
            TicketsRelationManager::class,
            NotesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListProjects::route('/'),
            'create' => CreateProject::route('/create'),
            'view' => ViewProject::route('/{record}'),
            'edit' => EditProject::route('/{record}/edit'),
            // Hapus baris ini: 'gantt-chart' => Pages\ProjectGanttChart::route('/gantt-chart'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        $userIsSuperAdmin = auth()->user() && (
            (method_exists(auth()->user(), 'hasRole') && auth()->user()->hasRole('super_admin'))
            || (isset(auth()->user()->role) && auth()->user()->role === 'super_admin')
        );

        if (! $userIsSuperAdmin) {
            $query->whereHas('members', function (Builder $query) {
                $query->where('user_id', auth()->id());
            });
        }

        return $query;
    }
}
