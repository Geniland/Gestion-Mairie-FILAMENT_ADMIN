<?php

namespace App\Filament\Resources\Taxes\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class TaxeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                 Select::make('commune_id')
                ->relationship('commune', 'nom')
                ->required()
                ->searchable(),

                Select::make('contribuable_id')
                    ->relationship('contribuable', 'nom')
                    ->required()
                    ->searchable(),

                Select::make('type_taxe_id')
                    ->relationship('typeTaxe', 'nom')
                    ->required()
                    ->searchable(),

                Select::make('agent_id')
                    ->relationship('agent', 'nom')
                    ->nullable()
                    ->searchable(),
                TextInput::make('montant')
                    ->required()
                    ->numeric(),
                DatePicker::make('periode_debut')
                    ->required(),
                DatePicker::make('periode_fin')
                    ->required(),
                TextInput::make('statut')
                    ->required()
                    ->default('en_attente'),
            ]);
    }
}
