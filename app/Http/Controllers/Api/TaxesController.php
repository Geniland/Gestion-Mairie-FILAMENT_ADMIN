<?php

namespace App\Http\Controllers\Api;

use App\Models\Taxe;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class TaxesController extends Controller
{
    /**
     * Liste des taxes
     */
    public function index()
    {
        $taxes = Taxe::with([
            'commune',
            'contribuable',
            'typeTaxe',
            'agent',
            'details',
            'payement',
            'tickets'
        ])->paginate(15);

        return response()->json([
            'status' => true,
            'message' => 'Liste des taxes',
            'data' => $taxes
        ]);
    }

    /**
     * Créer une taxe
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'commune_id' => 'required|exists:communes,id',
            'contribuable_id' => 'required|exists:contribuables,id',
            'type_taxe_id' => 'required|exists:type_taxes,id',
            'agent_id' => 'required|exists:agents,id',
            'montant' => 'required|numeric|min:0',
            'periode_debut' => 'required|date',
            'periode_fin' => 'required|date|after_or_equal:periode_debut',
            'statut' => 'required|string'
        ]);

        $taxe = Taxe::create($data);

        return response()->json([
            'status' => true,
            'message' => 'Taxe créée avec succès',
            'data' => $taxe
        ], 201);
    }

    /**
     * Afficher une taxe spécifique
     */
    public function show($id)
    {
        $taxe = Taxe::with([
            'commune',
            'contribuable',
            'typeTaxe',
            'agent',
            'details',
            'payement',
            'tickets'
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

    /**
     * Mettre à jour une taxe
     */
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
            'type_taxe_id' => 'required|exists:type_taxes,id',
            'agent_id' => 'required|exists:agents,id',
            'montant' => 'required|numeric|min:0',
            'periode_debut' => 'required|date',
            'periode_fin' => 'required|date|after_or_equal:periode_debut',
            'statut' => 'required|string'
        ]);

        $taxe->update($data);

        return response()->json([
            'status' => true,
            'message' => 'Taxe mise à jour avec succès',
            'data' => $taxe
        ]);
    }

    /**
     * Supprimer une taxe
     */
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
            'message' => 'Taxe supprimée avec succès'
        ]);
    }
}