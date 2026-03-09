<?php

namespace App\Filament\Widgets;

use App\Models\Taxe;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Support\Facades\DB;

class TopTaxes extends TableWidget
{
    protected static ?string $heading = 'Top 5 des taxes les plus payées';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Taxe::query()
                    ->join('tickets', 'taxes.id', '=', 'tickets.taxe_id')
                    ->join('types_taxes', 'taxes.type_taxe_id', '=', 'types_taxes.id')
                    ->select(
                        'taxes.id',
                        'types_taxes.nom as taxe',
                        DB::raw('COUNT(tickets.id) as total_paiements'),
                        DB::raw('SUM(taxes.montant) as total_montant')
                    )
                    ->groupBy(
                        'taxes.id',
                        'types_taxes.nom'
                    )
                    ->orderByDesc('total_paiements')
                    ->limit(5)
            )

            ->columns([

                Tables\Columns\TextColumn::make('taxe')
                    ->label('Taxe'),

                Tables\Columns\TextColumn::make('total_paiements')
                    ->label('Paiements')
                    ->badge(),

                Tables\Columns\TextColumn::make('total_montant')
                    ->label('Montant')
                    ->money('XOF'),

            ]);
    }
}