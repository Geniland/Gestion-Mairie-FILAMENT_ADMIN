<?php

namespace App\Filament\Resources\Contribuables\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class ContribuableForm
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
                TextInput::make('telephone')
                    ->tel()
                    ->required(),
                TextInput::make('type')
                    ->required(),
                TextInput::make('numero_identifiant')
                    ->default(null),
                TextInput::make('adresse')
                    ->default(null),
            ]);
    }
}
