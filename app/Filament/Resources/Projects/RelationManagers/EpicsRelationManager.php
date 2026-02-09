<?php

namespace App\Filament\Resources\Projects\RelationManagers;

use Filament\Schemas\Schema;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\RichEditor;
use Filament\Tables\Columns\TextColumn;
use Filament\Actions\CreateAction;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Forms;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class EpicsRelationManager extends RelationManager
{
    protected static string $relationship = 'epics';

    public static function getBadge(Model $ownerRecord, string $pageClass): ?string
    {
        return $ownerRecord->epics_count ?? $ownerRecord->epics()->count();
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required()
                    ->maxLength(255)
                    ->label('Epic Name'),
                TextInput::make('sort_order')
                    ->numeric()
                    ->default(0)
                    ->label('Sort Order')
                    ->helperText('Lower numbers appear first'),
                DatePicker::make('start_date')
                    ->label('Start Date')
                    ->nullable(),
                DatePicker::make('end_date')
                    ->label('End Date')
                    ->nullable(),
                RichEditor::make('description')
                    ->columnSpanFull()
                    ->nullable(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                TextColumn::make('sort_order')
                    ->label('Order')
                    ->sortable(),
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('start_date')
                    ->date()
                    ->sortable(),
                TextColumn::make('end_date')
                    ->date()
                    ->sortable(),
                TextColumn::make('tickets_count')
                    ->counts('tickets')
                    ->label('Tickets'),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                CreateAction::make()
                    ->visible(function () {
                        $project = $this->getOwnerRecord();

                        // Super admin can always create
                        if (auth()->user()->hasRole(['super_admin'])) {
                            return true;
                        }

                        // Check if user is a member of the project
                        return $project->members()->where('users.id', auth()->id())->exists();
                    }),
            ])
            ->recordActions([
                EditAction::make()
                    ->visible(function ($record) {
                        // Super admin can always edit
                        if (auth()->user()->hasRole(['super_admin'])) {
                            return true;
                        }

                        // Check if user is a member of the project
                        return $record->project->members()->where('users.id', auth()->id())->exists();
                    }),
                DeleteAction::make()
                    ->visible(function ($record) {
                        // Super admin can always delete
                        if (auth()->user()->hasRole(['super_admin'])) {
                            return true;
                        }

                        // Check if user is a member of the project
                        return $record->project->members()->where('users.id', auth()->id())->exists();
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('sort_order', 'asc');
    }
}
