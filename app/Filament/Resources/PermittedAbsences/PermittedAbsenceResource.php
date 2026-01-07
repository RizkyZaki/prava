<?php

namespace App\Filament\Resources\PermittedAbsences;

use App\Filament\Resources\PermittedAbsences\Pages\CreatePermittedAbsence;
use App\Filament\Resources\PermittedAbsences\Pages\EditPermittedAbsence;
use App\Filament\Resources\PermittedAbsences\Pages\ListPermittedAbsences;
use App\Filament\Resources\PermittedAbsences\Schemas\PermittedAbsenceForm;
use App\Filament\Resources\PermittedAbsences\Tables\PermittedAbsencesTable;
use App\Models\PermittedAbsence;
use BackedEnum;
use Filament\Resources\Resource;
use UnitEnum;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class PermittedAbsenceResource extends Resource
{
    protected static ?string $model = PermittedAbsence::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $navigationLabel = 'Perizinan';

    protected static ?string $modelLabel = 'Perizinan';

    protected static ?string $pluralModelLabel = 'Perizinan';

    protected static UnitEnum|string|null $navigationGroup = 'Kepegawaian';

    protected static ?int $navigationSort = 3;

    public static function form(Schema $schema): Schema
    {
        return PermittedAbsenceForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PermittedAbsencesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPermittedAbsences::route('/'),
            'create' => CreatePermittedAbsence::route('/create'),
            'edit' => EditPermittedAbsence::route('/{record}/edit'),
        ];
    }
}
