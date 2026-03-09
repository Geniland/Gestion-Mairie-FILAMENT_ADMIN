<?php

namespace App\Filament\Resources\Payements\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class PayementForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
            // Select::make('taxe_id')
            //     ->relationship('taxe', 'reference')
            //     ->searchable()
            //     ->required(),

            // Select::make('taxe_id')
            //     ->label('Taxe')
            //     ->relationship('taxe', 'id')
            //     ->getOptionLabelFromRecordUsing(function ($record) {
            //         return $record->typeTaxe->nom . ' - ' . $record->montant;
            //     })
            //     ->searchable()
            //     ->required(),

            Select::make('commune_id')
                    ->relationship('commune', 'nom')
                    ->searchable()
                    ->preload()
                    ->required(),

            Select::make('taxe_id')
                ->relationship('taxe.typeTaxe', 'nom'),

            Select::make('contribuable_id')
                ->relationship('contribuable', 'nom')
                ->searchable()
                ->required(),

            Select::make('agent_id')
                ->relationship('agent', 'nom')
                ->required(),

            // TextInput::make('montant')
            //     ->numeric()
            //     ->required(),

            Select::make('mode_payement')
                ->options([
                    'cash' => 'Cash',
                    'tmoney' => 'TMoney',
                    'flooz' => 'Flooz',
                    'banque' => 'Banque',
                ])
                ->required(),

            TextInput::make('reference_transaction')
                ->label('Référence transaction')
                ->visible(fn ($get) =>
                    $get('mode_payement') !== 'cash'
                ),
                TextInput::make('montant')
                    ->required()
                    ->numeric(),
               
                TextInput::make('reference')
                    ->default(null),
                DatePicker::make('date_payement')
                    ->required(),
            ]);
    }
}
