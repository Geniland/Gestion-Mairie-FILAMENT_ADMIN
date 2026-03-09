<?php

namespace App\Filament\Resources\Contribuables;

use App\Filament\Resources\Contribuables\Pages\CreateContribuable;
use App\Filament\Resources\Contribuables\Pages\EditContribuable;
use App\Filament\Resources\Contribuables\Pages\ListContribuables;
use App\Filament\Resources\Contribuables\Schemas\ContribuableForm;
use App\Filament\Resources\Contribuables\Tables\ContribuablesTable;
use App\Models\Contribuable;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ContribuableResource extends Resource
{
    protected static ?string $model = Contribuable::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'nom';

    public static function form(Schema $schema): Schema
    {
        return ContribuableForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ContribuablesTable::configure($table);
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
            'index' => ListContribuables::route('/'),
            'create' => CreateContribuable::route('/create'),
            'edit' => EditContribuable::route('/{record}/edit'),
        ];
    }
}
