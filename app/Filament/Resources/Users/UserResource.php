<?php

namespace App\Filament\Resources\Users;

use App\Filament\Resources\Users\Pages\CreateUser;
use App\Filament\Resources\Users\Pages\EditUser;
use App\Filament\Resources\Users\Pages\ListUsers;
use App\Filament\Resources\Users\RelationManagers\ProjectsRelationManager;
use App\Models\User;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Hash;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationLabel = 'Users';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                TextInput::make('email')
                    ->email()
                    ->required()
                    ->unique(
                        ignoreRecord: true
                    )
                    ->maxLength(255),
                DateTimePicker::make('email_verified_at'),

                TextInput::make('fingerprint_id')
                    ->label('Fingerprint ID')
                    ->numeric()
                    ->unique(ignoreRecord: true)
                    ->helperText('ID dari mesin fingerprint/face recognition (contoh: 6120004)')
                    ->placeholder('Contoh: 6120004'),

                TextInput::make('password')
                    ->password()
                    ->dehydrateStateUsing(fn ($state) => ! empty($state) ? Hash::make($state) : null
                    )
                    ->dehydrated(fn ($state) => ! empty($state))
                    ->required(fn (string $operation): bool => in_array($operation, ['create', 'attach.createOption']))
                    ->maxLength(255),
                Select::make('roles')
                    ->relationship('roles', 'name')
                    ->multiple()
                    ->preload()
                    ->searchable(),
                Toggle::make('is_active')
                    ->label('Active')
                    ->helperText('Inactive users cannot log in to the system')
                    ->default(true),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('email')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('fingerprint_id')
                    ->label('Fingerprint ID')
                    ->sortable()
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('roles.name')
                    ->label('Roles')
                    ->badge()
                    ->separator(',')
                    ->tooltip(fn (User $record): string => $record->roles->pluck('name')->join(', ') ?: 'No Roles')
                    ->sortable(),

                TextColumn::make('projects_count')
                    ->label('Projects')
                    ->counts('projects')
                    ->tooltip(fn (User $record): string => $record->projects->pluck('name')->join(', ') ?: 'No Projects')
                    ->sortable(),

                TextColumn::make('assigned_tickets_count')
                    ->label('Assigned Tickets')
                    ->counts('assignedTickets')
                    ->tooltip('Number of tickets assigned to this user')
                    ->sortable(),

                TextColumn::make('created_tickets_count')
                    ->label('Created Tickets')
                    ->getStateUsing(function (User $record): int {
                        return $record->createdTickets()->count();
                    })
                    ->tooltip('Number of tickets created by this user')
                    ->sortable(),

                IconColumn::make('is_active')
                    ->label('Status')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger')
                    ->tooltip(fn (User $record): string => $record->is_active ? 'Active' : 'Inactive')
                    ->sortable(),

                TextColumn::make('email_verified_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Filter::make('has_projects')
                    ->label('Has Projects')
                    ->query(fn (Builder $query): Builder => $query->whereHas('projects')),

                Filter::make('has_assigned_tickets')
                    ->label('Has Assigned Tickets')
                    ->query(fn (Builder $query): Builder => $query->whereHas('assignedTickets')),

                Filter::make('has_created_tickets')
                    ->label('Has Created Tickets')
                    ->query(fn (Builder $query): Builder => $query->whereHas('createdTickets')),

                // Filter by role
                SelectFilter::make('roles')
                    ->relationship('roles', 'name')
                    ->multiple()
                    ->searchable()
                    ->preload(),

                Filter::make('email_unverified')
                    ->label('Email Unverified')
                    ->query(fn (Builder $query): Builder => $query->whereNull('email_verified_at')),

                Filter::make('active')
                    ->label('Active Users')
                    ->query(fn (Builder $query): Builder => $query->where('is_active', true)),

                Filter::make('inactive')
                    ->label('Inactive Users')
                    ->query(fn (Builder $query): Builder => $query->where('is_active', false)),
            ])
            ->recordActions([
                EditAction::make(),
                ViewAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),

                    // Bulk action to activate users
                    BulkAction::make('activate')
                        ->label('Activate Users')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->requiresConfirmation()
                        ->action(function ($records) {
                            $records->each->update(['is_active' => true]);
                        })
                        ->deselectRecordsAfterCompletion(),

                    // Bulk action to deactivate users
                    BulkAction::make('deactivate')
                        ->label('Deactivate Users')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->action(function ($records) {
                            $records->each->update(['is_active' => false]);
                        })
                        ->deselectRecordsAfterCompletion(),

                    // Bulk action to assign role
                    BulkAction::make('assignRole')
                        ->label('Assign Role')
                        ->icon('heroicon-o-shield-check')
                        ->form([
                            Select::make('roles')
                                ->label('Roles')
                                ->relationship('roles', 'name')
                                ->multiple()
                                ->preload()
                                ->searchable()
                                ->required(),

                            Radio::make('role_mode')
                                ->label('Assignment Mode')
                                ->options([
                                    'replace' => 'Replace existing roles',
                                    'add' => 'Add to existing roles',
                                ])
                                ->default('add')
                                ->required(),
                        ])
                        ->action(function (array $data, $records) {
                            foreach ($records as $record) {
                                if ($data['role_mode'] === 'replace') {
                                    $record->roles()->sync($data['roles']);
                                } else {
                                    $record->roles()->syncWithoutDetaching($data['roles']);
                                }
                            }
                        }),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            ProjectsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListUsers::route('/'),
            'create' => CreateUser::route('/create'),
            'edit' => EditUser::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }
}
