<?php

namespace App\Http\Controllers\Api;

use App\Models\TaxeDetails;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class TaxeDetailsController extends Controller
{
    /**
     * Liste des détails de taxes
     */
    public function index()
    {
        $details = TaxeDetails::with(['taxe', 'commune'])->paginate(15);

        return response()->json([
            'success' => true,
            'message' => 'Liste des détails de taxes',
            'data' => $details
        ]);
    }

    /**
     * Créer un nouveau détail de taxe
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'commune_id' => 'required|exists:communes,id',
            'taxe_id' => 'required|exists:taxes,id',
            'details' => 'required|array'
        ]);

        $detail = TaxeDetails::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Détail de taxe créé avec succès',
            'data' => $detail
        ], 201);
    }

    /**
     * Afficher un détail spécifique
     */
    public function show($id)
    {
        $detail = TaxeDetails::with(['taxe', 'commune'])->find($id);

        if (!$detail) {
            return response()->json([
                'success' => false,
                'message' => 'Détail de taxe non trouvé'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $detail
        ]);
    }

    /**
     * Mettre à jour un détail de taxe
     */
    public function update(Request $request, $id)
    {
        $detail = TaxeDetails::find($id);

        if (!$detail) {
            return response()->json([
                'success' => false,
                'message' => 'Détail de taxe non trouvé'
            ], 404);
        }

        $data = $request->validate([
            'commune_id' => 'required|exists:communes,id',
            'taxe_id' => 'required|exists:taxes,id',
            'details' => 'required|array'
        ]);

        $detail->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Détail de taxe mis à jour avec succès',
            'data' => $detail
        ]);
    }

    /**
     * Supprimer un détail de taxe
     */
    public function destroy($id)
    {
        $detail = TaxeDetails::find($id);

        if (!$detail) {
            return response()->json([
                'success' => false,
                'message' => 'Détail de taxe non trouvé'
            ], 404);
        }

        $detail->delete();

        return response()->json([
            'success' => true,
            'message' => 'Détail de taxe supprimé avec succès'
        ]);
    }
}