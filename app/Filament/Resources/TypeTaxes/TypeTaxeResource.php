<?php

namespace App\Filament\Resources\TypeTaxes;

use App\Filament\Resources\TypeTaxes\Pages\CreateTypeTaxe;
use App\Filament\Resources\TypeTaxes\Pages\EditTypeTaxe;
use App\Filament\Resources\TypeTaxes\Pages\ListTypeTaxes;
use App\Filament\Resources\TypeTaxes\Schemas\TypeTaxeForm;
use App\Filament\Resources\TypeTaxes\Tables\TypeTaxesTable;
use App\Models\TypeTaxe;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class TypeTaxeResource extends Resource
{
    protected static ?string $model = TypeTaxe::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'nom';

    public static function form(Schema $schema): Schema
    {
        return TypeTaxeForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TypeTaxesTable::configure($table);
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
            'index' => ListTypeTaxes::route('/'),
            'create' => CreateTypeTaxe::route('/create'),
            'edit' => EditTypeTaxe::route('/{record}/edit'),
        ];
    }
}
