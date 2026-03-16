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
                    ->join('types_taxes', 'taxes.type_taxe_id', '=', 'types_taxes.id')
                    ->join('payements', 'taxes.id', '=', 'payements.taxe_id')
                    ->where('taxes.statut', 'payee')
                    ->select(
                        DB::raw('MIN(taxes.id) as id'),
                        'types_taxes.nom as taxe',
                        DB::raw('COUNT(payements.id) as total_paiements'),
                        DB::raw('SUM(payements.montant) as total_montant')
                    )
                    ->groupBy('types_taxes.id', 'types_taxes.nom')
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
        public static function canView(): bool
{
    $agent = auth()->guard('web')->user();

    return $agent && $agent->isSuperAdmin();
}
}