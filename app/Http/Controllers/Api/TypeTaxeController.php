<?php

namespace App\Http\Controllers\Api;

use App\Models\TypeTaxe;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class TypeTaxeController extends Controller
{
    /**
     * Liste des types de taxes
     */
    public function index()
    {
        $types = TypeTaxe::with(['commune', 'taxe'])->paginate(15);

        return response()->json([
            'success' => true,
            'message' => 'Liste des types de taxes',
            'data' => $types
        ]);
    }

    /**
     * Créer un type de taxe
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'commune_id' => 'required|exists:communes,id',
            'nom' => 'required|string|max:255',
            'description' => 'nullable|string',
            'montant_base' => 'required|numeric|min:0',
            'periode' => 'required|string|max:100',
            'actif' => 'boolean'
        ]);

        $type = TypeTaxe::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Type de taxe créé avec succès',
            'data' => $type
        ], 201);
    }

    /**
     * Afficher un type de taxe
     */
    public function show($id)
    {
        $type = TypeTaxe::with(['commune', 'taxe'])->find($id);

        if (!$type) {
            return response()->json([
                'success' => false,
                'message' => 'Type de taxe non trouvé'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $type
        ]);
    }

    /**
     * Mettre à jour un type de taxe
     */
    public function update(Request $request, $id)
    {
        $type = TypeTaxe::find($id);

        if (!$type) {
            return response()->json([
                'success' => false,
                'message' => 'Type de taxe non trouvé'
            ], 404);
        }

        $data = $request->validate([
            'commune_id' => 'required|exists:communes,id',
            'nom' => 'required|string|max:255',
            'description' => 'nullable|string',
            'montant_base' => 'required|numeric|min:0',
            'periode' => 'required|string|max:100',
            'actif' => 'boolean'
        ]);

        $type->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Type de taxe mis à jour avec succès',
            'data' => $type
        ]);
    }

    /**
     * Supprimer un type de taxe
     */
    public function destroy($id)
    {
        $type = TypeTaxe::find($id);

        if (!$type) {
            return response()->json([
                'success' => false,
                'message' => 'Type de taxe non trouvé'
            ], 404);
        }

        $type->delete();

        return response()->json([
            'success' => true,
            'message' => 'Type de taxe supprimé avec succès'
        ]);
    }
}