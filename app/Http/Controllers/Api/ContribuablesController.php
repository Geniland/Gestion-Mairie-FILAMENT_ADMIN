<?php

namespace App\Http\Controllers\Api;

use App\Models\Contribuable;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ContribuablesController extends Controller
{
    /**
     * Liste des contribuables
     */
    public function index()
    {
        $contribuables = Contribuable::with('commune')->paginate(15);

        return response()->json([
            'status' => true,
            'message' => 'Liste des contribuables',
            'data' => $contribuables
        ]);
    }

    /**
     * Créer un contribuable
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'commune_id' => 'required|exists:communes,id',
            'nom' => 'required|string|max:255',
            'telephone' => 'required|string|max:20',
            'type' => 'required|string|max:100',
            'numero_identifiant' => 'required|string|max:100|unique:contribuables,numero_identifiant',
            'adresse' => 'nullable|string|max:255'
        ]);

        $contribuable = Contribuable::create($data);

        return response()->json([
            'status' => true,
            'message' => 'Contribuable créé avec succès',
            'data' => $contribuable
        ], 201);
    }

    /**
     * Afficher un contribuable
     */
    public function show($id)
    {
        $contribuable = Contribuable::with([
            'commune',
            'taxe',
            'payement',
            'amendes'
        ])->find($id);

        if (!$contribuable) {
            return response()->json([
                'status' => false,
                'message' => 'Contribuable non trouvé'
            ], 404);
        }

        return response()->json([
            'status' => true,
            'data' => $contribuable
        ]);
    }

    /**
     * Mettre à jour un contribuable
     */
    public function update(Request $request, $id)
    {
        $contribuable = Contribuable::find($id);

        if (!$contribuable) {
            return response()->json([
                'status' => false,
                'message' => 'Contribuable non trouvé'
            ], 404);
        }

        $data = $request->validate([
            'commune_id' => 'required|exists:communes,id',
            'nom' => 'required|string|max:255',
            'telephone' => 'required|string|max:20',
            'type' => 'required|string|max:100',
            'numero_identifiant' => 'required|string|max:100|unique:contribuables,numero_identifiant,' . $id,
            'adresse' => 'nullable|string|max:255'
        ]);

        $contribuable->update($data);

        return response()->json([
            'status' => true,
            'message' => 'Contribuable mis à jour avec succès',
            'data' => $contribuable
        ]);
    }

    /**
     * Supprimer un contribuable
     */
    public function destroy($id)
    {
        $contribuable = Contribuable::find($id);

        if (!$contribuable) {
            return response()->json([
                'status' => false,
                'message' => 'Contribuable non trouvé'
            ], 404);
        }

        $contribuable->delete();

        return response()->json([
            'status' => true,
            'message' => 'Contribuable supprimé avec succès'
        ]);
    }
}