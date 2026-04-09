<?php

namespace App\Http\Controllers\Api;

use App\Models\Payement;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class PayementsController extends Controller
{
    /**
     * Liste des paiements
     */
    public function index()
    {
        
        $user = auth()->user();
        
        $query = Payement::with([
            'taxe.typeTaxe',
            'agent',
            'commune',
            'contribuable'
        ]);

        if ($user && method_exists($user, 'isAgent') && $user->isAgent()) {
            $query->where('agent_id', $user->id);
        }

        $payements = $query->latest()->paginate(15);

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
        $user = auth()->user();
        
        $data = $request->validate([
            'taxe_id' => 'required|exists:taxes,id',
            'commune_id' => 'required|exists:communes,id',
            'contribuable_id' => 'required|exists:contribuables,id',
            'montant' => 'required|numeric|min:0',
            'mode_payement' => 'required|string|max:100',
            'quartier_id' => 'required|exists:quartiers,id',
            'date_payement' => 'required|date',
            'reference_transaction' => 'nullable|string|max:255|unique:payements,reference_transaction',
            'reference' => 'nullable|string|max:255'
        ]);

        $data['agent_id'] = $user->id;

        $payement = Payement::create($data);

        return response()->json([
            'status' => true,
            'message' => 'Paiement enregistré avec succès',
            'data' => $payement->load([
                'taxe.typeTaxe',
                'commune',
                'contribuable',
                'agent'
            ])
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