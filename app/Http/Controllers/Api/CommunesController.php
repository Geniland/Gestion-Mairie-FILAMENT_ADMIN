<?php

namespace App\Http\Controllers\Api;

use App\Models\Commune;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Quartier;

class CommunesController extends Controller
{
    /**
     * Liste des communes
     */
    public function index()
    {
        $communes = Commune::paginate(15);

        return response()->json([
            'status' => true,
            'message' => 'Liste des communes',
            'data' => $communes
        ]);
    }

    /**
     * Enregistrer une commune
     */
    // public function store(Request $request)
    // {
    //     $data = $request->validate([
    //         'nom' => 'required|string|max:255',
    //         'region' => 'required|string|max:255',
    //         'quartier' => 'nullable|string|max:255'
    //     ]);

    //     $commune = Commune::create($data);

    //     return response()->json([
    //         'status' => true,
    //         'message' => 'Commune créée avec succès',
    //         'data' => $commune
    //     ], 201);
    // }
    




public function store(Request $request)
{
    $data = $request->validate([
        'nom' => 'required|string|max:255',
        'region' => 'required|string|max:255',
        'quartiers' => 'nullable|array',
        'quartiers.*' => 'string|max:255'
    ]);

    // créer commune
    $commune = Commune::create([
        'nom' => $data['nom'],
        'region' => $data['region']
    ]);

    // créer quartiers
    if (!empty($data['quartiers'])) {
        foreach ($data['quartiers'] as $quartier) {
            Quartier::create([
                'commune_id' => $commune->id,
                'nom' => $quartier
            ]);
        }
    }

    return response()->json([
        'status' => true,
        'message' => 'Commune créée avec quartiers',
        'data' => $commune->load('quartiers')
    ], 201);
}




    /**
     * Afficher une commune spécifique
     */
    public function show($id)
    {
        $commune = Commune::with([
            'contribuable',
            'taxe',
            'amendes',
            'agents'
        ])->find($id);

        if (!$commune) {
            return response()->json([
                'status' => false,
                'message' => 'Commune non trouvée'
            ], 404);
        }

        return response()->json([
            'status' => true,
            'data' => $commune
        ]);
    }

    /**
     * Mettre à jour une commune
     */
    public function update(Request $request, $id)
    {
        $commune = Commune::find($id);

        if (!$commune) {
            return response()->json([
                'status' => false,
                'message' => 'Commune non trouvée'
            ], 404);
        }

        $data = $request->validate([
            'nom' => 'required|string|max:255',
            'region' => 'required|string|max:255',
            'quartier' => 'nullable|string|max:255'
        ]);

        $commune->update($data);

        return response()->json([
            'status' => true,
            'message' => 'Commune mise à jour avec succès',
            'data' => $commune
        ]);
    }

    /**
     * Supprimer une commune
     */
    public function destroy($id)
    {
        $commune = Commune::find($id);

        if (!$commune) {
            return response()->json([
                'status' => false,
                'message' => 'Commune non trouvée'
            ], 404);
        }

        $commune->delete();

        return response()->json([
            'status' => true,
            'message' => 'Commune supprimée avec succès'
        ]);
    }
}