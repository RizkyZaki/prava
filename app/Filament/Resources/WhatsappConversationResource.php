<?php

namespace App\Filament\Resources;

use App\Filament\Resources\WhatsappConversationResource\Pages;
use App\Filament\Resources\WhatsappConversationResource\RelationManagers;
use App\Models\WhatsappConversation;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class WhatsappConversationResource extends Resource
{
    protected static ?string $model = WhatsappConversation::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-clock';
    protected static string|\UnitEnum|null $navigationGroup = 'Customer Service';
    protected static ?string $navigationLabel = 'Riwayat WhatsApp';
    protected static ?string $modelLabel = 'Conversation';
    protected static ?string $pluralModelLabel = 'Conversations';
    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Forms\Components\TextInput::make('phone')->label('Phone')->disabled(),
                Forms\Components\TextInput::make('customer_name')->label('Customer Name')->disabled(),
                Forms\Components\TextInput::make('mode')->label('Mode')->disabled(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('phone')
                    ->label('Phone')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('customer_name')
                    ->label('Nama Customer')
                    ->searchable()
                    ->sortable()
                    ->placeholder('—'),

                Tables\Columns\TextColumn::make('mode')
                    ->label('Mode')
                    ->badge()
                    ->color(fn (string $state) => match ($state) {
                        'ai' => 'success',
                        'admin' => 'warning',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('assignedAdmin.name')
                    ->label('Assigned To')
                    ->placeholder('—'),

                Tables\Columns\TextColumn::make('messages_count')
                    ->label('Messages')
                    ->counts('messages')
                    ->sortable(),

                Tables\Columns\TextColumn::make('last_message_at')
                    ->label('Last Message')
                    ->dateTime('d M Y H:i')
                    ->sortable(),

                Tables\Columns\TextColumn::make('ended_at')
                    ->label('Ended At')
                    ->dateTime('d M Y H:i')
                    ->placeholder('Active')
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Started')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('last_message_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('mode')
                    ->options([
                        'ai' => 'AI',
                        'admin' => 'Admin',
                        'selection' => 'Selection',
                    ]),

                Tables\Filters\Filter::make('date_range')
                    ->form([
                        Forms\Components\DatePicker::make('from')
                            ->label('From'),
                        Forms\Components\DatePicker::make('until')
                            ->label('Until'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['from'], fn (Builder $q, $date) => $q->whereDate('created_at', '>=', $date))
                            ->when($data['until'], fn (Builder $q, $date) => $q->whereDate('created_at', '<=', $date));
                    }),

                Tables\Filters\SelectFilter::make('assigned_to')
                    ->relationship('assignedAdmin', 'name')
                    ->label('Assigned Admin'),

                Tables\Filters\TernaryFilter::make('ended')
                    ->label('Status')
                    ->trueLabel('Ended')
                    ->falseLabel('Active')
                    ->queries(
                        true: fn (Builder $q) => $q->whereNotNull('ended_at'),
                        false: fn (Builder $q) => $q->whereNull('ended_at'),
                    ),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\WhatsappMessagesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListWhatsappConversations::route('/'),
            'view' => Pages\ViewWhatsappConversation::route('/{record}'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }
}
