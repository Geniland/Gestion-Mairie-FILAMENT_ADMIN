<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Models\Tickets;
use App\Models\Contribuable;
use Illuminate\Support\Facades\DB;

class RevenueStats extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        $ticketsTotal = Tickets::count();

        $recettes = Tickets::join('taxes', 'tickets.taxe_id', '=', 'taxes.id')
            ->sum('taxes.montant');

        $ticketsToday = Tickets::whereDate('created_at', today())->count();

        $recettesToday = Tickets::join('taxes', 'tickets.taxe_id', '=', 'taxes.id')
            ->whereDate('tickets.created_at', today())
            ->sum('taxes.montant');

        return [

            Stat::make('Tickets émis', $ticketsTotal)
                ->description($ticketsToday . ' aujourd\'hui')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('primary')
                ->icon('heroicon-o-ticket'),

            Stat::make('Recettes totales', number_format($recettes, 0, ',', ' ') . ' FCFA')
                ->description(number_format($recettesToday, 0, ',', ' ') . ' aujourd\'hui')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('success')
                ->icon('heroicon-o-banknotes'),

            Stat::make('Contribuables', Contribuable::count())
                ->description('Personnes enregistrées')
                ->descriptionIcon('heroicon-m-users')
                ->color('warning')
                ->icon('heroicon-o-users'),
        ];
    }
}