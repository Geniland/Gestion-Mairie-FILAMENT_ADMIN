<?php

namespace App\Http\Controllers\Api;

use App\Models\Quartier;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class QuartierController extends Controller
{
    /**
     * Liste de tous les quartiers
     */
    public function index()
    {
        $quartiers = Quartier::with('commune')->paginate(15);

        return response()->json([
            'status' => true,
            'message' => 'Liste des quartiers',
            'data' => $quartiers
        ]);
    }

    /**
     * Créer un quartier
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'commune_id' => 'required|exists:communes,id',
            'nom' => 'required|string|max:255'
        ]);

        $quartier = Quartier::create($data);

        return response()->json([
            'status' => true,
            'message' => 'Quartier créé avec succès',
            'data' => $quartier
        ], 201);
    }

    /**
     * Afficher un quartier
     */
    public function show($id)
    {
        $quartier = Quartier::with('commune')->find($id);

        if (!$quartier) {
            return response()->json([
                'status' => false,
                'message' => 'Quartier non trouvé'
            ], 404);
        }

        return response()->json([
            'status' => true,
            'data' => $quartier
        ]);
    }

    /**
     * Mettre à jour un quartier
     */
    public function update(Request $request, $id)
    {
        $quartier = Quartier::find($id);

        if (!$quartier) {
            return response()->json([
                'status' => false,
                'message' => 'Quartier non trouvé'
            ], 404);
        }

        $data = $request->validate([
            'commune_id' => 'required|exists:communes,id',
            'nom' => 'required|string|max:255'
        ]);

        $quartier->update($data);

        return response()->json([
            'status' => true,
            'message' => 'Quartier mis à jour avec succès',
            'data' => $quartier
        ]);
    }

    /**
     * Supprimer un quartier
     */
    public function destroy($id)
    {
        $quartier = Quartier::find($id);

        if (!$quartier) {
            return response()->json([
                'status' => false,
                'message' => 'Quartier non trouvé'
            ], 404);
        }

        $quartier->delete();

        return response()->json([
            'status' => true,
            'message' => 'Quartier supprimé avec succès'
        ]);
    }

    /**
     * 🔥 Récupérer les quartiers d'une commune (IMPORTANT POUR TON FRONT)
     */
    public function getByCommune($communeId)
    {
        $quartiers = Quartier::where('commune_id', $communeId)->get();

        return response()->json([
            'status' => true,
            'data' => $quartiers
        ]);
    }
}