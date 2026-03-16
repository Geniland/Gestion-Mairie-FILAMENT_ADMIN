<?php

namespace App\Filament\Resources\Tickets\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class TicketsForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
            Select::make('taxe_id')
                    ->relationship('taxe.typeTaxe', 'nom'),



            // Select::make('contribuable_id')
            //     ->relationship('contribuable', 'nom')
            //     ->searchable()
            //     ->required(),

            // Select::make('taxe.contribuable.nom')
            //     ->label('Contribuable')
            //     ->searchable(),


            Select::make('agent_id')
                ->relationship('agent', 'nom')
                ->default(fn () => auth()->user()->id) // ✅ pré-rempli avec l'agent connecté
                ->dehydrated()
                ->required(),

            Select::make('contribuable_id')
                    ->relationship('contribuable', 'nom')
                    ->required()
                    ->searchable(),

            Select::make('commune_id')
                ->relationship('commune','nom')
                ->required(),

            TextInput::make('numero_ticket')
                ->default(fn () => 'TCK-'.rand(100000,999999))
                ->required(),

           TextInput::make('qr_hash')
                ->default(fn () => md5(uniqid()))
                ->disabled(),

            DatePicker::make('date_expiration')
                ->required(),

            Select::make('statut')
                ->options([
                    'valide'=>'Valide',
                    'annule'=>'Annulé',
                    'expire'=>'Expiré'
                ])
                ->default('valide'),




              

               
                ]);
    }
}
