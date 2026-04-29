<?php

namespace App\Filament\Resources\EtatCivilRequests;

use App\Filament\Resources\EtatCivilRequests\Pages\CreateEtatCivilRequest;
use App\Filament\Resources\EtatCivilRequests\Pages\EditEtatCivilRequest;
use App\Filament\Resources\EtatCivilRequests\Pages\ListEtatCivilRequests;
use App\Filament\Resources\EtatCivilRequests\Schemas\EtatCivilRequestForm;
use App\Filament\Resources\EtatCivilRequests\Tables\EtatCivilRequestsTable;
use App\Models\EtatCivilRequest;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class EtatCivilRequestResource extends Resource
{
    protected static ?string $model = EtatCivilRequest::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationLabel = 'Demandes État Civil';

    protected static ?string $modelLabel = 'Demande État Civil';

    protected static ?string $pluralModelLabel = 'Demandes État Civil';

    public static function form(Schema $schema): Schema
    {
        return EtatCivilRequestForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return EtatCivilRequestsTable::configure($table);
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
            'index' => ListEtatCivilRequests::route('/'),
            'create' => CreateEtatCivilRequest::route('/create'),
            'edit' => EditEtatCivilRequest::route('/{record}/edit'),
        ];
    }
}
