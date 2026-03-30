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

            $agentsQuery = Agents::query()
                ->leftJoinSub($ticketsSub, 'tickets_summary', function ($join) {
                    $join->on('agents.id', '=', 'tickets_summary.agent_id');
                })
                ->leftJoinSub($payementsSub, 'payements_summary', function ($join) {
                    $join->on('agents.id', '=', 'payements_summary.agent_id');
                })
                ->select(
                    'agents.id',
                    'agents.nom',
                    'agents.email',
                    'agents.telephone',
                    DB::raw('COALESCE(tickets_summary.total_tickets, 0) as total_tickets'),
                    DB::raw('COALESCE(payements_summary.total_collecte, 0) as total_collecte')
                );

            // 🔒 Filtrage si pas super admin
            if (!$user->isSuperAdmin()) {
                $agentsQuery->where('agents.id', $user->id);
            }

            // 🔽 Tri par performance
            $agents = $agentsQuery
                ->orderByDesc('total_collecte')
                ->orderByDesc('total_tickets')
                ->get();

            // 🏆 Meilleur agent (le premier après tri)
            $topAgent = $agents->first();

            return response()->json([
                'success' => true,

                // 📊 Liste complète
                'agents' => $agents,

                // 🏆 Agent le plus performant
                'top_agent' => $topAgent ? [
                    'id' => $topAgent->id,
                    'nom' => $topAgent->nom,
                    'email' => $topAgent->email,
                    'telephone' => $topAgent->telephone,
                    'total_tickets' => $topAgent->total_tickets,
                    'total_collecte' => $topAgent->total_collecte,
                ] : null
            ]);
        }

    /**
     * Récupérer les statistiques de revenus
     */
    public function revenueStats()
        {
            $user = auth()->user();
            $query = Payement::query();

            $payementsQuery = Payement::query();
            $taxesQuery = Taxe::query();

            if (!$user->isSuperAdmin()) {
                $query->where('agent_id', $user->id);
            }

            $today = (clone $query)->whereDate('created_at', today())->sum('montant');
            $week = (clone $query)->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->sum('montant');
            $month = (clone $query)->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->sum('montant');
            $year = (clone $query)->whereYear('created_at', now()->year)->sum('montant');

             // Total attendu (toutes les taxes créées)
            $totalAttendu = (clone $taxesQuery)->sum('montant');

            // Total payé (tous les paiements)
            $totalPaye = (clone $payementsQuery)->sum('montant');

            // Calcul taux recouvrement global
            $tauxRecouvrement = 0;
            if ($totalAttendu > 0) {
                $tauxRecouvrement = round(($totalPaye / $totalAttendu) * 100, 2);
            }

            return response()->json([
                'success' => true,
                'revenu_jour' => $today,
                'revenu_semaine' => $week,
                'revenu_mois' => $month,
                'revenu_annee' => $year,
                'taux_recouvrement' => $tauxRecouvrement,
            ]);
        }

    /**
     * Top 5 des taxes les plus payées
     */
    public function topTaxes()
        {
            $user = auth()->user();

            /*
            |--------------------------------------------------------------------------
            | 🏆 TOP 5 TAXES PAYÉES
            |--------------------------------------------------------------------------
            */
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


            /*
            |--------------------------------------------------------------------------
            | 🔴 TAXES IMPAYÉES (par commune)
            |--------------------------------------------------------------------------
            */
            $unpaidTaxes = Taxe::query()
                ->join('types_taxes', 'taxes.type_taxe_id', '=', 'types_taxes.id')
                ->join('contribuables', 'taxes.contribuable_id', '=', 'contribuables.id')
                ->leftJoin('agents', 'taxes.agent_id', '=', 'agents.id')
                ->where('taxes.statut', '!=', 'payee')

                // 🔥 filtrage par commune de l'utilisateur
                ->where('taxes.commune_id', $user->commune_id)

                ->select(
                    'taxes.id',
                    'types_taxes.nom as taxe',
                    'contribuables.nom as contribuable',
                    'agents.nom as agent',
                    'taxes.montant',
                    'taxes.periode_debut',
                    'taxes.periode_fin',
                    'taxes.statut'
                )
                ->orderByDesc('taxes.created_at')
                ->get();


            /*
            |--------------------------------------------------------------------------
            | 🎯 RESPONSE
            |--------------------------------------------------------------------------
            */
            return response()->json([
                'success' => true,

                // 🏆 Top taxes
                'top_taxes' => $topTaxes,

                // 🔴 Taxes impayées
                'taxes_impayees' => $unpaidTaxes
            ]);
        }


    public function stats()
        {
            // Total attendu = somme de toutes les taxes créées
            $totalAttendu = Taxe::sum('montant');

            // Total payé = somme de tous les paiements enregistrés
            $totalPaye = Payement::sum('montant');

            // Eviter division par zéro
            $tauxRecouvrement = 0;
            if ($totalAttendu > 0) {
                $tauxRecouvrement = round(($totalPaye / $totalAttendu) * 100, 2);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'total_attendu' => $totalAttendu,
                    'total_paye' => $totalPaye,
                    'taux_recouvrement' => $tauxRecouvrement
                ]
            ]);
        }



    public function bestZone()
    {
        $user = auth()->user();

        $query = Payement::query()
            ->join('quartiers', 'payements.quartier_id', '=', 'quartiers.id')
            ->select(
                'quartiers.nom as zone',
                DB::raw('SUM(payements.montant) as total_montant'),
                DB::raw('COUNT(payements.id) as total_payements')
            );

        // si ce n'est pas un super admin => filtrer par agent connecté
        if (!$user->isSuperAdmin()) {
            $query->where('payements.agent_id', $user->id);
        }

        $bestZone = $query
            ->groupBy('quartiers.nom')
            ->orderByDesc('total_montant')
            ->first();

        return response()->json([
            'zone' => $bestZone->zone ?? 'Aucune donnée',
            'montant' => $bestZone->total_montant ?? 0,
            'payements' => $bestZone->total_payements ?? 0,
        ]);
    }




    public function riskFraud()
    {
        $user = auth()->user();

        $query = Taxe::with(['typeTaxe', 'payement']);

        // si pas admin => voir seulement ses taxes
        if (!$user->isSuperAdmin()) {
            $query->where('agent_id', $user->id);
        }

        $taxes = $query->get();

        $alertes = 0;
        $anomalies = 0;
        $taxesSuspects = 0;

        foreach ($taxes as $taxe) {

            $montantTaxe = (float) $taxe->montant;
            $montantBase = $taxe->typeTaxe ? (float) $taxe->typeTaxe->montant : 0;

            // total encaissé sur cette taxe
            $totalPaye = (float) $taxe->payement->sum('montant');

            // Cas suspect 1 : taxe sans type taxe
            if (!$taxe->typeTaxe) {
                $alertes++;
                $taxesSuspects++;
                continue;
            }

            // Cas suspect 2 : montant taxe différent du montant base type taxe
            if ($montantTaxe != $montantBase) {
                $anomalies++;
                $taxesSuspects++;
            }

            // Cas suspect 3 : montant payé différent du montant taxe
            if ($totalPaye != $montantTaxe) {
                $alertes++;
                $taxesSuspects++;
            }

            // Cas suspect 4 : paiement supérieur au montant taxe
            if ($totalPaye > $montantTaxe) {
                $anomalies++;
                $taxesSuspects++;
            }
        }

        return response()->json([
            'success' => true,
            'data' => [
                'alertes' => $alertes,
                'anomalies' => $anomalies,
                'taxes_suspects' => $taxesSuspects
            ]
        ]);
    }



}