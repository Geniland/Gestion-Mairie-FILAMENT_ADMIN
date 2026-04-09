<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use App\Models\Contribuable;
use App\Models\Commune;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
 use Illuminate\Support\Facades\Auth;

class ContribuablesController extends Controller
{
    /**
     * Liste des contribuables (Agents terrain)
     */
    public function index()
    {
        $user = Auth::user();

        $query = Contribuable::with('commune');

        // Agent terrain → seulement ses contribuables
        if ($user->role === 'agent') {
            $query->where('agent_id', $user->id);
        }

        // Tri du plus récent au moins récent
        $query->orderBy('created_at', 'desc');

        $contribuables = $query->get();

        return response()->json([
            'status' => true,
            'message' => 'Liste des contribuables (terrain)',
            'data' => $contribuables
        ]);
    }

    /**
     * Liste des utilisateurs (Portail Public)
     */
    public function listPublicUsers()
    {
        $users = User::orderBy('created_at', 'desc')->get();

        return response()->json([
            'status' => true,
            'message' => 'Liste des utilisateurs du portail public',
            'data' => $users
        ]);
    }

    /**
     * Créer un contribuable
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        
        $data = $request->validate([
            'commune_id' => 'required|exists:communes,id',
            'nom' => 'required|string|max:255',
            'telephone' => 'required|string|max:20',
            'type' => 'required|string|max:100',
            'numero_identifiant' => 'required|string|max:100|unique:contribuables,numero_identifiant',
            'adresse' => 'nullable|string|max:255'
        ]);

        $data['agent_id'] = $user->id;

        $contribuable = Contribuable::create($data);

        return response()->json([
            'status' => true,
            'message' => 'Contribuable créé avec succès',
            'data' => $contribuable->load('commune')
        ], 201);
    }

    /**
     * Afficher un contribuable
     */
    public function show($id)
    {
        $user = Auth::user();

        $contribuable = Contribuable::with('commune')->find($id);

        if (!$contribuable) {
            return response()->json([
                'status' => false,
                'message' => 'Contribuable non trouvé'
            ], 404);
        }

        // Vérifier si l'agent a le droit de voir ce contribuable
        if ($user->isAgent() && $contribuable->agent_id !== $user->id) {
            return response()->json([
                'status' => false,
                'message' => 'Accès non autorisé'
            ], 403);
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
            'data' => $contribuable->load('commune')
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