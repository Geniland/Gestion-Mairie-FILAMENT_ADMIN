<?php

namespace App\Http\Controllers\Api;

use App\Models\Payement;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class PayementController extends Controller
{
    /**
     * Liste des paiements
     */
    public function index()
    {
        $payements = Payement::with([
            'taxe',
            'agent',
            'commune',
            'contribuable'
        ])->paginate(15);

        return response()->json([
            'status' => true,
            'message' => 'Liste des paiements',
            'data' => $payements
        ]);
    }

    /**
     * Enregistrer un paiement
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'taxe_id' => 'required|exists:taxes,id',
            'agent_id' => 'required|exists:agents,id',
            'commune_id' => 'required|exists:communes,id',
            'contribuable_id' => 'required|exists:contribuables,id',
            'montant' => 'required|numeric|min:0',
            'mode_payement' => 'required|string|max:100',
            'date_payement' => 'required|date',
            'reference_transaction' => 'nullable|string|max:255',
            'reference' => 'nullable|string|max:255'
        ]);

        $payement = Payement::create($data);

        return response()->json([
            'status' => true,
            'message' => 'Paiement enregistré avec succès',
            'data' => $payement
        ], 201);
    }

    /**
     * Afficher un paiement
     */
    public function show($id)
    {
        $payement = Payement::with([
            'taxe',
            'agent',
            'commune',
            'contribuable'
        ])->find($id);

        if (!$payement) {
            return response()->json([
                'status' => false,
                'message' => 'Paiement non trouvé'
            ], 404);
        }

        return response()->json([
            'status' => true,
            'data' => $payement
        ]);
    }

    /**
     * Mettre à jour un paiement
     */
    public function update(Request $request, $id)
    {
        $payement = Payement::find($id);

        if (!$payement) {
            return response()->json([
                'status' => false,
                'message' => 'Paiement non trouvé'
            ], 404);
        }

        $data = $request->validate([
            'taxe_id' => 'required|exists:taxes,id',
            'agent_id' => 'required|exists:agents,id',
            'commune_id' => 'required|exists:communes,id',
            'contribuable_id' => 'required|exists:contribuables,id',
            'montant' => 'required|numeric|min:0',
            'mode_payement' => 'required|string|max:100',
            'date_payement' => 'required|date',
            'reference_transaction' => 'nullable|string|max:255',
            'reference' => 'nullable|string|max:255'
        ]);

        $payement->update($data);

        return response()->json([
            'status' => true,
            'message' => 'Paiement mis à jour avec succès',
            'data' => $payement
        ]);
    }

    /**
     * Supprimer un paiement
     */
    public function destroy($id)
    {
        $payement = Payement::find($id);

        if (!$payement) {
            return response()->json([
                'status' => false,
                'message' => 'Paiement non trouvé'
            ], 404);
        }

        $payement->delete();

        return response()->json([
            'status' => true,
            'message' => 'Paiement supprimé avec succès'
        ]);
    }
}