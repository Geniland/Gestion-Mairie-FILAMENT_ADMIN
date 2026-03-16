<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Models\Payement;
use Carbon\Carbon;

class RevenueStats extends BaseWidget
{
    protected function getStats(): array
    {
        $user = auth()->user();

        // requête de base
        $query = Payement::query();

        // si ce n'est pas super admin → filtrer par agent
        if (!$user->isSuperAdmin()) {
            $query->where('agent_id', $user->id);
        }

        $today = (clone $query)
            ->whereDate('created_at', today())
            ->sum('montant');

        $week = (clone $query)
            ->whereBetween('created_at', [
                now()->startOfWeek(),
                now()->endOfWeek()
            ])
            ->sum('montant');

        $month = (clone $query)
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->sum('montant');

        $year = (clone $query)
            ->whereYear('created_at', now()->year)
            ->sum('montant');

        return [

            Stat::make('Recette du Jour', number_format($today, 0, ',', ' ') . ' FCFA')
                ->icon('heroicon-m-banknotes')
                ->color('success')
                ->description('vs hier: ' . $this->getVariation($query, $today))
                ->descriptionIcon($this->getVariationIcon($query, $today))
                ->chart([3,5,8,12,9,14,18]),

            Stat::make('Recette de la Semaine', number_format($week, 0, ',', ' ') . ' FCFA')
                ->icon('heroicon-m-banknotes')
                ->color('info')
                ->description('Objectif: ' . number_format($week * 1.2, 0, ',', ' ') . ' FCFA')
                ->chart([5,8,12,16,20,25,30]),

            Stat::make('Recette du Mois', number_format($month, 0, ',', ' ') . ' FCFA')
                ->icon('heroicon-m-banknotes')
                ->color('warning')
                ->chart([10,15,18,22,30,35,40]),

            Stat::make('Recette de l\'Année', number_format($year, 0, ',', ' ') . ' FCFA')
                ->icon('heroicon-m-shield-check')
                ->color('primary')
                ->chart([50,70,90,120,160,200,275]),
        ];
    }

    private function getVariation($query, $current): string
    {
        $yesterday = (clone $query)
            ->whereDate('created_at', Carbon::yesterday())
            ->sum('montant');

        if ($yesterday == 0) return 'Nouveau';

        $variation = round((($current - $yesterday) / $yesterday) * 100, 1);

        return ($variation > 0 ? '+' : '') . $variation . '%';
    }

    private function getVariationIcon($query, $current): string
    {
        $yesterday = (clone $query)
            ->whereDate('created_at', Carbon::yesterday())
            ->sum('montant');

        if ($yesterday == 0) return 'heroicon-m-sparkles';

        return $current >= $yesterday
            ? 'heroicon-m-arrow-trending-up'
            : 'heroicon-m-arrow-trending-down';
    }

    protected function getColumns(): int
    {
        return 4;
    }
}