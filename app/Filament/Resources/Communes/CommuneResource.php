<?php

namespace App\Filament\Resources\Communes;

use App\Filament\Resources\Communes\Pages\CreateCommune;
use App\Filament\Resources\Communes\Pages\EditCommune;
use App\Filament\Resources\Communes\Pages\ListCommunes;
use App\Filament\Resources\Communes\Schemas\CommuneForm;
use App\Filament\Resources\Communes\Tables\CommunesTable;
use App\Models\Commune;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class CommuneResource extends Resource
{
    protected static ?string $model = Commune::class;

    protected static string|BackedEnum|null $navigationIcon = "heroicon-o-building-office";

    protected static ?string $recordTitleAttribute = 'nom';

    public static function form(Schema $schema): Schema
    {
        return CommuneForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CommunesTable::configure($table);
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
            'index' => ListCommunes::route('/'),
            'create' => CreateCommune::route('/create'),
            'edit' => EditCommune::route('/{record}/edit'),
        ];
    }
}
