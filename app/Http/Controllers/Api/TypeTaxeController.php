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
    public function index(Request $request)
    {
        $query = TypeTaxe::with('commune');
        
        // Filtrer par commune selon l'utilisateur connecté
        if (auth('api_agents')->check()) {
            $authAgent = auth('api_agents')->user();
            if (!$authAgent->isSuperAdmin()) {
                $query->where('commune_id', $authAgent->commune_id);
            }
        } elseif (auth('api_users')->check()) {
            $authUser = auth('api_users')->user();
            $query->where('commune_id', $authUser->commune_id);
        }
        
        // Si ce n'est pas une requête admin, on ne montre que les actifs
        if (!$request->has('all')) {
            $query->where('actif', true);
        }
        
        $types = $query->paginate(15);

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
        $authAgent = auth('api_agents')->user();
        
        $data = $request->validate([
            'commune_id' => 'required|exists:communes,id',
            'nom' => 'required|string|max:255',
            'description' => 'nullable|string',
            'montant_base' => 'required|numeric|min:0',
            'periode' => 'required|string|max:100',
            'actif' => 'boolean'
        ]);

        // Si ce n'est pas un super admin, forcer la commune de l'agent connecté
        if (!$authAgent->isSuperAdmin()) {
            $data['commune_id'] = $authAgent->commune_id;
        }

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

        // Vérification de l'accès
        if (auth('api_agents')->check()) {
            $authAgent = auth('api_agents')->user();
            if (!$authAgent->isSuperAdmin() && $type->commune_id !== $authAgent->commune_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Accès refusé'
                ], 403);
            }
        } elseif (auth('api_users')->check()) {
            $authUser = auth('api_users')->user();
            if ($type->commune_id !== $authUser->commune_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Accès refusé'
                ], 403);
            }
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

        $authAgent = auth('api_agents')->user();
        
        // Vérification : seul le super admin peut modifier des types de taxes d'autres communes
        if (!$authAgent->isSuperAdmin() && $type->commune_id !== $authAgent->commune_id) {
            return response()->json([
                'success' => false,
                'message' => 'Accès refusé'
            ], 403);
        }

        $data = $request->validate([
            'commune_id' => 'required|exists:communes,id',
            'nom' => 'required|string|max:255',
            'description' => 'nullable|string',
            'montant_base' => 'required|numeric|min:0',
            'periode' => 'required|string|max:100',
            'actif' => 'boolean'
        ]);

        // Si ce n'est pas un super admin, forcer la commune de l'agent connecté
        if (!$authAgent->isSuperAdmin()) {
            $data['commune_id'] = $authAgent->commune_id;
        }

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

        $authAgent = auth('api_agents')->user();
        
        // Vérification : seul le super admin peut supprimer des types de taxes d'autres communes
        if (!$authAgent->isSuperAdmin() && $type->commune_id !== $authAgent->commune_id) {
            return response()->json([
                'success' => false,
                'message' => 'Accès refusé'
            ], 403);
        }

        $type->delete();

        return response()->json([
            'success' => true,
            'message' => 'Type de taxe supprimé avec succès'
        ]);
    }
}