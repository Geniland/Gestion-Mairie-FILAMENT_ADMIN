<?php

namespace App\Http\Controllers\Api;

use App\Models\PublicTaxe;
use App\Models\Taxe;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class TaxesController extends Controller
{
    /**
     * Liste des taxes (Terrain)
     */
    public function index()
    {
        $user = Auth::user();

        $query = Taxe::with([
            'commune',
            'contribuable',
            'typeTaxe',
            'agent'
        ]);

        // 🔥 ADMIN voit tout
        if ($user->role !== 'admin') {
            $query->where('agent_id', $user->id);
        }

        $taxes = $query->latest()->paginate(15);

        return response()->json([
            'status' => true,
            'data' => $taxes
        ]);
    }

    /**
     * Liste des taxes publiques à valider (Site Web)
     */
    public function listPublicTaxes()
    {
        $taxes = PublicTaxe::with(['typeTaxe', 'user'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'status' => true,
            'message' => 'Liste des taxes publiques (site web)',
            'data' => $taxes
        ]);
    }

    /**
     * Approuver une taxe publique
     */
    public function approvePublicTaxe(Request $request, $id)
    {
        $taxe = PublicTaxe::findOrFail($id);
        $taxe->update([
            'status' => 'approuvee',
            'commentaire_admin' => $request->commentaire_admin
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Taxe approuvée avec succès'
        ]);
    }

    /**
     * Rejeter une taxe publique
     */
    public function rejectPublicTaxe(Request $request, $id)
    {
        $taxe = PublicTaxe::findOrFail($id);
        $taxe->update([
            'status' => 'rejetee',
            'commentaire_admin' => $request->commentaire_admin
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Taxe rejetée'
        ]);
    }

    public function store(Request $request)
    {
        $user = Auth::user();

        $data = $request->validate([
            'commune_id' => 'required|exists:communes,id',
            'contribuable_id' => 'required|exists:contribuables,id',
            'type_taxe_id' => 'required|exists:types_taxes,id',
            'montant' => 'required|numeric|min:0',
            'periode_debut' => 'required|date',
            'periode_fin' => 'required|date|after_or_equal:periode_debut',
            'statut' => 'required|string'
        ]);

        // 🔥 Vérifier si une taxe identique existe déjà pour éviter les doublons
        $existing = Taxe::where('contribuable_id', $data['contribuable_id'])
            ->where('type_taxe_id', $data['type_taxe_id'])
            ->where('periode_debut', $data['periode_debut'])
            ->first();

        if ($existing) {
            return response()->json([
                'status' => true,
                'message' => 'Cette taxe existe déjà.',
                'data' => $existing->load(['contribuable', 'typeTaxe'])
            ], 200); // On renvoie 200 au lieu de créer un doublon
        }

        // 🔥 FORCER agent_id (sécurité)
        $data['agent_id'] = $user->id;

        $taxe = Taxe::create($data);

        return response()->json([
            'status' => true,
            'data' => $taxe->load(['contribuable', 'typeTaxe'])
        ], 201);
    }

    public function show($id)
    {
        $taxe = Taxe::with([
            'commune',
            'contribuable',
            'typeTaxe',
            'agent'
        ])->find($id);

        if (!$taxe) {
            return response()->json([
                'status' => false,
                'message' => 'Taxe non trouvée'
            ], 404);
        }

        return response()->json([
            'status' => true,
            'data' => $taxe
        ]);
    }

    public function update(Request $request, $id)
    {
        $taxe = Taxe::find($id);

        if (!$taxe) {
            return response()->json([
                'status' => false,
                'message' => 'Taxe non trouvée'
            ], 404);
        }

        $data = $request->validate([
            'commune_id' => 'required|exists:communes,id',
            'contribuable_id' => 'required|exists:contribuables,id',
            'type_taxe_id' => 'required|exists:types_taxes,id',
            'montant' => 'required|numeric|min:0',
            'periode_debut' => 'required|date',
            'periode_fin' => 'required|date|after_or_equal:periode_debut',
            'statut' => 'required|string'
        ]);

        $taxe->update($data);

        return response()->json([
            'status' => true,
            'data' => $taxe->load(['contribuable', 'typeTaxe'])
        ]);
    }

    public function destroy($id)
    {
        $taxe = Taxe::find($id);

        if (!$taxe) {
            return response()->json([
                'status' => false,
                'message' => 'Taxe non trouvée'
            ], 404);
        }

        $taxe->delete();

        return response()->json([
            'status' => true,
            'message' => 'Supprimé avec succès'
        ]);
    }
}