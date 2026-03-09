<?php

namespace App\Filament\Resources\TypeTaxes\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use App\Models\TypeTaxe;


class TypeTaxeForm
{


    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('commune_id')
                    ->relationship('commune', 'nom')
                    ->searchable()
                    ->preload()
                    ->required(),
                TextInput::make('nom')
                    ->required(),
                Textarea::make('description')
                    ->default(null)
                    ->columnSpanFull(),
                TextInput::make('montant_base')
                    ->numeric()
                    ->default(null),
                TextInput::make('periode')
                    ->default(null),
                Toggle::make('actif')
                    ->required(),
            ]);
    }
    
}
