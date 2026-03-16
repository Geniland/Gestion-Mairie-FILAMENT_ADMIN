<?php

namespace App\Filament\Resources\Payements\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use App\Models\Taxe;

class PayementForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([

                // Choix de la taxe impayée
                Select::make('taxe_id')
                    ->label('Taxe impayée')
                    ->relationship(
                        name: 'taxe',
                        titleAttribute: 'id',
                        modifyQueryUsing: fn ($query) => $query->where('statut', 'en_attente')
                    )
                    ->getOptionLabelFromRecordUsing(fn ($record) =>
                        $record->contribuable->nom .
                        ' | ' .
                        $record->typeTaxe->nom .
                        ' | ' .
                        number_format($record->montant, 0, ',', ' ') . ' FCFA'
                    )
                    ->searchable()
                    ->preload()
                    ->required()
                    ->live()
                    ->afterStateUpdated(function ($state, $set) {
                        $taxe = Taxe::find($state);
                        if ($taxe) {
                            // Remplit les champs automatiquement
                            $set('contribuable_id', $taxe->contribuable_id);
                            $set('montant', $taxe->montant);
                            $set('commune_id', $taxe->commune_id);
                        }
                    }),

                // Ces champs sont "hidden" mais doivent être envoyés à la DB
                Select::make('commune_id')
                ->relationship('commune', 'nom')  
                ->required()
                ->searchable(),


                Select::make('contribuable_id')    
                    ->relationship('contribuable', 'nom')
                    ->required()
                    ->searchable()
                    ->dehydrated(),

                TextInput::make('montant')
                    ->numeric()
                    ->required()
                    ->dehydrated(), // 🔑 important pour envoyer même si désactivé
                    // Ne pas mettre disabled()

                Select::make('agent_id')
                    ->relationship('agent', 'nom')
                    ->required(),

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
                    ->visible(fn ($get) => $get('mode_payement') !== 'cash'),

                TextInput::make('reference')
                    ->default(null),

                DatePicker::make('date_payement')
                    ->required()
                    ->default(today()),

            ]);
    }
}