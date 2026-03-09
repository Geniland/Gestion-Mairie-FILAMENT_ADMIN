<?php

namespace App\Filament\Widgets;

use App\Models\Taxe;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Support\Facades\DB;

class TopTaxes extends TableWidget
{
    protected static ?string $heading = '🏆 Top 5 des taxes les plus payées';

    protected int|string|array $columnSpan = 'full';

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
                        DB::raw('COALESCE(SUM(taxes.montant),0) as total_montant')
                    )
                    ->groupBy('taxes.id', 'types_taxes.nom')
                    ->orderByDesc('total_montant')
                    ->limit(5)
            )

            ->columns([

                Tables\Columns\TextColumn::make('taxe')
                    ->label('Taxe')
                    ->weight('bold')
                    ->searchable(),

                Tables\Columns\TextColumn::make('total_paiements')
                    ->label('Paiements')
                    ->badge()
                    ->color('primary')
                    ->sortable(),

                Tables\Columns\TextColumn::make('total_montant')
                    ->label('Montant collecté')
                    ->money('XOF')
                    ->color(fn ($state) => $state > 100000 ? 'success' : 'warning')
                    ->sortable(),

            ])

            ->striped()
            ->defaultSort('total_montant', 'desc')
            ->paginated(false);
    }
}