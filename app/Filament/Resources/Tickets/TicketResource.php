<?php

namespace App\Filament\Resources\Tickets;

use Filament\Schemas\Schema;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\RichEditor\EditorCommand;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Filament\Actions\ViewAction;
use Filament\Actions\EditAction;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use App\Filament\Resources\Tickets\Pages\ListTickets;
use App\Filament\Resources\Tickets\Pages\CreateTicket;
use App\Filament\Resources\Tickets\Pages\ViewTicket;
use App\Filament\Resources\Tickets\Pages\EditTicket;
use App\Filament\Resources\TicketResource\Pages;
use App\Models\Project;
use App\Models\Ticket;
use App\Models\TicketStatus;
use App\Models\TicketPriority;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;
use Filament\Support\Enums\Width;
use Livewire\Component;
use App\Models\Epic;

class TicketResource extends Resource
{
    protected static ?string $model = Ticket::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-ticket';

    protected static ?string $navigationLabel = 'Tickets';

    protected static string | \UnitEnum | null $navigationGroup = 'Project Management';

    protected static ?int $navigationSort = 5;

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        $user = auth()->user();
        if (!($user && ($user->hasRole('super_admin') || $user->hasRole('finance')))) {
            // Hanya tampilkan ticket yang di-assign ke user, dibuat user, atau project-nya user adalah member
            $query->where(function ($query) {
                $query->whereHas('assignees', function ($query) {
                        $query->where('users.id', auth()->id());
                    })
                    ->orWhere('created_by', auth()->id())
                    ->orWhereHas('project.members', function ($query) {
                        $query->where('users.id', auth()->id());
                    });
            });
        }

        return $query;
    }

    public static function form(Schema $schema): Schema
    {
        $projectId = request()->query('project_id') ?? request()->input('project_id');
        $statusId = request()->query('ticket_status_id') ?? request()->input('ticket_status_id');

        return $schema
            ->components([
                Select::make('project_id')
                    ->label('Project')
                    ->options(function () {
                        if (auth()->user()->can('view_any_project')) {
                            return Project::pluck('name', 'id')->toArray();
                        }
                        return auth()->user()->projects()->pluck('name', 'projects.id')->toArray();
                    })
                    ->default($projectId)
                    ->required()
                    ->searchable()
                    ->preload()
                    ->live()
                    ->afterStateUpdated(function (callable $set) {
                        $set('ticket_status_id', null);
                        $set('assignees', []);
                        $set('epic_id', null);
                    }),

                Select::make('ticket_status_id')
                    ->label('Status')
                    ->options(function ($get) {
                        $projectId = $get('project_id');
                        if (! $projectId) {
                            return [];
                        }

                        return TicketStatus::where('project_id', $projectId)
                            ->pluck('name', 'id')
                            ->toArray();
                    })
                    ->default($statusId)
                    ->required()
                    ->searchable()
                    ->preload(),

                Select::make('priority_id')
                    ->label('Priority')
                    ->options(TicketPriority::pluck('name', 'id')->toArray())
                    ->searchable()
                    ->preload()
                    ->nullable(),

                Select::make('epic_id')
                    ->label('Epic')
                    ->options(function (callable $get) {
                        $projectId = $get('project_id');

                        if (!$projectId) {
                            return [];
                        }

                        return Epic::where('project_id', $projectId)
                            ->pluck('name', 'id')
                            ->toArray();
                    })
                    ->searchable()
                    ->preload()
                    ->nullable()
                    ->hidden(fn (callable $get): bool => !$get('project_id')),

                TextInput::make('name')
                    ->label('Ticket Name')
                    ->required()
                    ->maxLength(255),

                RichEditor::make('description')
                    ->label('Description')
                    ->fileAttachmentsDisk('public')
                    ->fileAttachmentsDirectory('attachments')
                    ->fileAttachmentsVisibility('public')
                    ->fileAttachmentsAcceptedFileTypes([
                        'image/jpeg',
                        'image/png',
                        'image/gif',
                        'image/webp',
                        'application/pdf',
                    ])
                    ->registerActions([
                        Action::make('attachFiles')
                            ->label(__('filament-forms::components.rich_editor.actions.attach_files.label'))
                            ->modalHeading(__('filament-forms::components.rich_editor.actions.attach_files.modal.heading'))
                            ->modalWidth(Width::Large)
                            ->fillForm(fn (array $arguments): array => [
                                'alt' => $arguments['alt'] ?? null,
                            ])
                            ->schema(fn (array $arguments, RichEditor $component): array => [
                                FileUpload::make('file')
                                    ->label(filled($arguments['src'] ?? null)
                                        ? __('filament-forms::components.rich_editor.actions.attach_files.modal.form.file.label.existing')
                                        : __('filament-forms::components.rich_editor.actions.attach_files.modal.form.file.label.new'))
                                    ->acceptedFileTypes($component->getFileAttachmentsAcceptedFileTypes())
                                    ->maxSize($component->getFileAttachmentsMaxSize())
                                    ->storeFiles(false)
                                    ->required(blank($arguments['src'] ?? null))
                                    ->hiddenLabel(blank($arguments['src'] ?? null)),
                                TextInput::make('alt')
                                    ->label(filled($arguments['src'] ?? null)
                                        ? __('filament-forms::components.rich_editor.actions.attach_files.modal.form.alt.label.existing')
                                        : __('filament-forms::components.rich_editor.actions.attach_files.modal.form.alt.label.new'))
                                    ->maxLength(1000),
                            ])
                            ->action(function (array $arguments, array $data, RichEditor $component, Component $livewire): void {
                                if ($data['file'] ?? null) {
                                    $file = $data['file'];
                                    $isImage = Str::startsWith($file->getMimeType(), 'image/');
                                    $fileName = $file->getClientOriginalName();

                                    // Store file permanently to public disk
                                    $directory = 'attachments';
                                    $storedPath = $file->store($directory, 'public');
                                    $src = asset('storage/' . $storedPath);
                                    $id = (string) Str::orderedUuid();
                                }

                                if (filled($arguments['src'] ?? null)) {
                                    if ($arguments['editorSelection']['type'] !== 'node') {
                                        $arguments['editorSelection']['type'] = 'node';
                                        $arguments['editorSelection']['anchor']--;
                                        unset($arguments['editorSelection']['head']);
                                    }

                                    $id ??= $arguments['id'] ?? null;
                                    $src ??= $arguments['src'];

                                    $component->runCommands(
                                        [
                                            EditorCommand::make('updateAttributes', arguments: [
                                                'image',
                                                [
                                                    'alt' => $data['alt'] ?? null,
                                                    'id' => $id,
                                                    'src' => $src,
                                                ],
                                            ]),
                                        ],
                                        editorSelection: $arguments['editorSelection'],
                                    );

                                    return;
                                }

                                if (blank($id ?? null) || blank($src ?? null)) {
                                    return;
                                }

                                if ($isImage ?? true) {
                                    $component->runCommands(
                                        [
                                            EditorCommand::make('insertContent', arguments: [[
                                                'type' => 'image',
                                                'attrs' => [
                                                    'alt' => $data['alt'] ?? null,
                                                    'id' => $id,
                                                    'src' => $src,
                                                ],
                                            ]]),
                                        ],
                                        editorSelection: $arguments['editorSelection'],
                                    );
                                } else {
                                    $label = $data['alt'] ?? ($fileName ?? 'Lampiran');
                                    $component->runCommands(
                                        [
                                            EditorCommand::make('insertContent', arguments: [
                                                '<a href="' . e($src) . '" target="_blank" rel="noopener noreferrer">📎 ' . e($label) . '</a> ',
                                            ]),
                                        ],
                                        editorSelection: $arguments['editorSelection'],
                                    );
                                }
                            }),
                    ])
                    ->toolbarButtons([
                        'attachFiles',
                        'blockquote',
                        'bold',
                        'bulletList',
                        'codeBlock',
                        'h2',
                        'h3',
                        'italic',
                        'link',
                        'orderedList',
                        'redo',
                        'strike',
                        'underline',
                        'undo',
                    ])
                    ->columnSpanFull(),

                // Multi-user assignment
                Select::make('assignees')
                    ->label('Assigned to')
                    ->multiple()
                    ->relationship(
                        name: 'assignees',
                        titleAttribute: 'name',
                        modifyQueryUsing: function (Builder $query, callable $get) {
                            $projectId = $get('project_id');
                            if (! $projectId) {
                                return $query->whereRaw('1 = 0');
                            }

                            $project = Project::find($projectId);
                            if (! $project) {
                                return $query->whereRaw('1 = 0');
                            }

                            return $query->whereHas('projects', function ($query) use ($projectId) {
                                $query->where('projects.id', $projectId);
                            });
                        }
                    )
                    ->searchable()
                    ->preload()
                    ->helperText('Select multiple users to assign this ticket to. Only project members can be assigned.')
                    ->hidden(fn (callable $get): bool => !$get('project_id'))
                    ->live(),

                DatePicker::make('start_date')
                    ->label('Start Date')
                    ->default(now())
                    ->nullable(),

                DatePicker::make('due_date')
                    ->label('Due Date')
                    ->nullable(),
                Select::make('created_by')
                    ->label('Created By')
                    ->relationship('creator', 'name')
                    ->disabled()
                    ->hiddenOn('create'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('uuid')
                    ->label('Ticket ID')
                    ->searchable()
                    ->copyable(),

                TextColumn::make('project.name')
                    ->label('Project')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('name')
                    ->label('Name')
                    ->searchable()
                    ->limit(30),

                TextColumn::make('status.name')
                    ->label('Status')
                    ->badge()
                    ->sortable(),

                TextColumn::make('priority.name')
                    ->label('Priority')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'High' => 'danger',
                        'Medium' => 'warning',
                        'Low' => 'success',
                        default => 'gray',
                    })
                    ->sortable()
                    ->default('—')
                    ->placeholder('No Priority'),

                // Display multiple assignees
                TextColumn::make('assignees.name')
                    ->label('Assign To')
                    ->badge()
                    ->separator(',')
                    ->limitList(2)
                    ->expandableLimitedList()
                    ->searchable(),

                TextColumn::make('creator.name')
                    ->label('Created By')
                    ->sortable()
                    ->searchable()
                    ->toggleable(),

                TextColumn::make('start_date')
                    ->label('Start Date')
                    ->date()
                    ->sortable(),

                TextColumn::make('due_date')
                    ->label('Due Date')
                    ->date()
                    ->sortable(),

                TextColumn::make('epic.name')
                    ->label('Epic')
                    ->sortable()
                    ->searchable()
                    ->default('—')
                    ->placeholder('No Epic'),

                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('project_id')
                    ->label('Project')
                    ->options(function () {
                        if (auth()->user()->hasRole(['super_admin'])) {
                            return Project::pluck('name', 'id')->toArray();
                        }

                        return auth()->user()->projects()->pluck('name', 'projects.id')->toArray();
                    })
                    ->searchable()
                    ->preload(),

                SelectFilter::make('ticket_status_id')
                    ->label('Status')
                    ->options(function () {
                        $projectId = request()->input('tableFilters.project_id');

                        if (!$projectId) {
                            return [];
                        }

                        return TicketStatus::where('project_id', $projectId)
                            ->pluck('name', 'id')
                            ->toArray();
                    })
                    ->searchable()
                    ->preload(),

                SelectFilter::make('epic_id')
                    ->label('Epic')
                    ->options(function () {
                        $projectId = request()->input('tableFilters.project_id');

                        if (!$projectId) {
                            return [];
                        }

                        return Epic::where('project_id', $projectId)
                            ->pluck('name', 'id')
                            ->toArray();
                    })
                    ->searchable()
                    ->preload(),

                SelectFilter::make('priority_id')
                    ->label('Priority')
                    ->options(TicketPriority::pluck('name', 'id')->toArray())
                    ->searchable()
                    ->preload(),

                // Filter by assignees
                SelectFilter::make('assignees')
                    ->label('Assignee')
                    ->relationship('assignees', 'name')
                    ->multiple()
                    ->searchable()
                    ->preload(),

                // Filter by creator
                SelectFilter::make('created_by')
                    ->label('Created By')
                    ->relationship('creator', 'name')
                    ->searchable()
                    ->preload(),

                Filter::make('due_date')
                    ->schema([
                        DatePicker::make('due_from'),
                        DatePicker::make('due_until'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['due_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('due_date', '>=', $date),
                            )
                            ->when(
                                $data['due_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('due_date', '<=', $date),
                            );
                    }),
            ])
            ->defaultSort('created_at', 'desc')
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                Action::make('copy')
                    ->label('Copy')
                    ->icon('heroicon-o-document-duplicate')
                    ->color('info')
                    ->action(function ($record, $livewire) {
                        // Redirect ke halaman create, dengan parameter copy_from
                        return $livewire->redirect(
                            static::getUrl('create', [
                                'copy_from' => $record->id,
                            ])
                        );
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->visible(auth()->user()->can('delete_ticket')),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [

        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListTickets::route('/'),
            'create' => CreateTicket::route('/create'),
            'view' => ViewTicket::route('/{record}'),
            'edit' => EditTicket::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        $query = static::getEloquentQuery();

        return $query->count();
    }
}
