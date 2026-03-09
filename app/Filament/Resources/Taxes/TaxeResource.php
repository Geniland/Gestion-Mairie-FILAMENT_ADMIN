<?php

namespace App\Filament\Resources\Taxes;

use App\Filament\Resources\Taxes\Pages\CreateTaxe;
use App\Filament\Resources\Taxes\Pages\EditTaxe;
use App\Filament\Resources\Taxes\Pages\ListTaxes;
use App\Filament\Resources\Taxes\Schemas\TaxeForm;
use App\Filament\Resources\Taxes\Tables\TaxesTable;
use App\Models\Taxe;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class TaxeResource extends Resource
{
    protected static ?string $model = Taxe::class;

    protected static string|BackedEnum|null $navigationIcon = "heroicon-o-banknotes";

    protected static ?string $recordTitleAttribute = 'type_taxe_id';

    public static function form(Schema $schema): Schema
    {
        return TaxeForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TaxesTable::configure($table);
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
            'index' => ListTaxes::route('/'),
            'create' => CreateTaxe::route('/create'),
            'edit' => EditTaxe::route('/{record}/edit'),
        ];
    }
}
