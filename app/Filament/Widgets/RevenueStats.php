<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Models\Tickets;
use App\Models\Contribuable;

class RevenueStats extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        $recettes = Tickets::join('taxes', 'tickets.taxe_id', '=', 'taxes.id')
            ->sum('taxes.montant');

        return [
            Stat::make('Tickets émis', Tickets::count())
                ->description('Nombre total de tickets')
                ->icon('heroicon-o-ticket'),

            Stat::make('Recettes', number_format($recettes, 0, ',', ' ') . ' FCFA')
                ->description('Total des paiements')
                ->icon('heroicon-o-banknotes'),

            Stat::make('Contribuables', Contribuable::count())
                ->description('Personnes enregistrées')
                ->icon('heroicon-o-users'),
        ];
    }
}