<?php

namespace App\Filament\Resources\WhatsappConversationResource\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class WhatsappMessagesRelationManager extends RelationManager
{
    protected static string $relationship = 'messages';

    protected static ?string $title = 'Messages';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('sender_type')
                    ->label('Sender')
                    ->badge()
                    ->color(fn (string $state) => match ($state) {
                        'customer' => 'info',
                        'admin' => 'success',
                        'ai' => 'warning',
                        'system' => 'gray',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('sender.name')
                    ->label('Admin')
                    ->placeholder('—'),

                Tables\Columns\TextColumn::make('body')
                    ->label('Message')
                    ->limit(80)
                    ->tooltip(fn ($record) => $record->body)
                    ->wrap(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Time')
                    ->dateTime('d M Y H:i:s')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'asc')
            ->paginated([25, 50, 100]);
    }

    public function isReadOnly(): bool
    {
        return true;
    }
}
