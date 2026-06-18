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
        $query = Quartier::with('commune');
        
        // Filtrer par commune si l'agent connecté n'est pas super admin
        if (auth('api_agents')->check()) {
            $authAgent = auth('api_agents')->user();
            if (!$authAgent->isSuperAdmin()) {
                $query->where('commune_id', $authAgent->commune_id);
            }
        }
        
        $quartiers = $query->paginate(15);

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
        $authAgent = auth('api_agents')->user();
        
        $data = $request->validate([
            'commune_id' => 'required|exists:communes,id',
            'nom' => 'required|string|max:255',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
        ]);

        // Si ce n'est pas un super admin, forcer la commune de l'agent connecté
        if (!$authAgent->isSuperAdmin()) {
            $data['commune_id'] = $authAgent->commune_id;
        }

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

        // Vérification d'accès
        if (auth('api_agents')->check()) {
            $authAgent = auth('api_agents')->user();
            if (!$authAgent->isSuperAdmin() && $quartier->commune_id !== $authAgent->commune_id) {
                return response()->json([
                    'status' => false,
                    'message' => 'Accès refusé'
                ], 403);
            }
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
        $authAgent = auth('api_agents')->user();
        
        $quartier = Quartier::find($id);

        if (!$quartier) {
            return response()->json([
                'status' => false,
                'message' => 'Quartier non trouvé'
            ], 404);
        }

        // Vérification d'accès
        if (!$authAgent->isSuperAdmin() && $quartier->commune_id !== $authAgent->commune_id) {
            return response()->json([
                'status' => false,
                'message' => 'Accès refusé'
            ], 403);
        }

        $data = $request->validate([
            'commune_id' => 'required|exists:communes,id',
            'nom' => 'required|string|max:255',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
        ]);

        // Si ce n'est pas un super admin, forcer la commune de l'agent connecté
        if (!$authAgent->isSuperAdmin()) {
            $data['commune_id'] = $authAgent->commune_id;
        }

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
        $authAgent = auth('api_agents')->user();
        
        $quartier = Quartier::find($id);

        if (!$quartier) {
            return response()->json([
                'status' => false,
                'message' => 'Quartier non trouvé'
            ], 404);
        }

        // Vérification d'accès
        if (!$authAgent->isSuperAdmin() && $quartier->commune_id !== $authAgent->commune_id) {
            return response()->json([
                'status' => false,
                'message' => 'Accès refusé'
            ], 403);
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