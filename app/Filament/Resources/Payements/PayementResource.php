<?php

namespace App\Filament\Resources\Payements;

use App\Filament\Resources\Payements\Pages\CreatePayement;
use App\Filament\Resources\Payements\Pages\EditPayement;
use App\Filament\Resources\Payements\Pages\ListPayements;
use App\Filament\Resources\Payements\Schemas\PayementForm;
use App\Filament\Resources\Payements\Tables\PayementsTable;
use App\Models\Payement;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class PayementResource extends Resource
{
    protected static ?string $model = Payement::class;

    protected static string|BackedEnum|null $navigationIcon = "heroicon-o-currency-dollar";

    protected static ?string $recordTitleAttribute = 'montant';

    public static function form(Schema $schema): Schema
    {
        return PayementForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PayementsTable::configure($table);
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
            'index' => ListPayements::route('/'),
            'create' => CreatePayement::route('/create'),
            'edit' => EditPayement::route('/{record}/edit'),
        ];
    }
}
