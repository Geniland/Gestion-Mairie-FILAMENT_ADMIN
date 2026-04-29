<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Payement;
use App\Models\Tickets;
use App\Models\Agents;
use App\Models\Taxe;

class MobileDashboardController extends Controller
{
    public function stats()
    {
        $user = auth()->user();

        $payementsQuery = Payement::query();
        $ticketsQuery = Tickets::query();
        $taxesQuery = Taxe::query();

        if ($user && method_exists($user, 'isAgent') && $user->isAgent()) {
            $payementsQuery->where('agent_id', $user->id);
            $ticketsQuery->where('agent_id', $user->id);
            $taxesQuery->where('agent_id', $user->id);
        }

        $totalPaye = $payementsQuery->sum('montant');
        $totalTickets = $ticketsQuery->count();
        $totalTaxes = $taxesQuery->count();

        $taxesPayees = (clone $taxesQuery)
            ->whereIn('statut', ['payee', 'payé', 'payée'])
            ->count();

        $tauxRecouvrement = $totalTaxes > 0
            ? round(($taxesPayees / $totalTaxes) * 100, 2)
            : 0;

        // $agentsActifs = Agents::count();
        $agentsActifs = Agents::where('is_blocked', false)->count();

        return response()->json([
            'status' => true,
            'message' => 'Statistiques dashboard',
            'data' => [
                'total_paye' => $totalPaye,
                'total_tickets' => $totalTickets,
                'total_taxes' => $totalTaxes,
                'taxes_payees' => $taxesPayees,
                'taux_recouvrement' => $tauxRecouvrement,
                'agents_actifs' => $agentsActifs
            ]
        ]);
    }
}