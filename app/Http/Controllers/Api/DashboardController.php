<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Agents;
use App\Models\Payement;
use App\Models\Taxe;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    /**
     * Récupérer la performance des agents
     */
    public function agentPerformance()
    {
        $user = auth()->user();

        $ticketsSub = DB::table('tickets')
            ->select('agent_id', DB::raw('COUNT(*) as total_tickets'))
            ->groupBy('agent_id');

        $payementsSub = DB::table('payements')
            ->select('agent_id', DB::raw('SUM(montant) as total_collecte'))
            ->groupBy('agent_id');

        $agents = Agents::query()
            ->leftJoinSub($ticketsSub, 'tickets_summary', function ($join) {
                $join->on('agents.id', '=', 'tickets_summary.agent_id');
            })
            ->leftJoinSub($payementsSub, 'payements_summary', function ($join) {
                $join->on('agents.id', '=', 'payements_summary.agent_id');
            })
            ->select(
                'agents.id',
                'agents.nom',
                DB::raw('COALESCE(tickets_summary.total_tickets, 0) as total_tickets'),
                DB::raw('COALESCE(payements_summary.total_collecte, 0) as total_collecte')
            )
            ->when(!$user->isSuperAdmin(), fn($q) => $q->where('agents.id', $user->id))
            ->orderByDesc('total_collecte')
            ->get();

        return response()->json([
            'success' => true,
            'agents' => $agents
        ]);
    }

    /**
     * Récupérer les statistiques de revenus
     */
    public function revenueStats()
    {
        $user = auth()->user();
        $query = Payement::query();

        if (!$user->isSuperAdmin()) {
            $query->where('agent_id', $user->id);
        }

        $today = (clone $query)->whereDate('created_at', today())->sum('montant');
        $week = (clone $query)->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->sum('montant');
        $month = (clone $query)->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->sum('montant');
        $year = (clone $query)->whereYear('created_at', now()->year)->sum('montant');

        return response()->json([
            'success' => true,
            'revenu_jour' => $today,
            'revenu_semaine' => $week,
            'revenu_mois' => $month,
            'revenu_annee' => $year,
        ]);
    }

    /**
     * Top 5 des taxes les plus payées
     */
    public function topTaxes()
    {
        $topTaxes = Taxe::query()
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
            ->get();

        return response()->json([
            'success' => true,
            'top_taxes' => $topTaxes
        ]);
    }
}