<?php

namespace App\Http\Controllers\Api;

use App\Models\Agents;
use App\Models\Commune;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use App\Http\Controllers\Controller;

class AgentsController extends Controller
{
    public function index()
    {
        $authAgent = auth('api_agents')->user();
        
        if ($authAgent->isSuperAdmin()) {
            $agents = Agents::with('commune')->paginate(15);
        } else {
            $agents = Agents::with('commune')
                ->where('commune_id', $authAgent->commune_id)
                ->paginate(15);
        }

        return response()->json([
            'status' => true,
            'data' => $agents
        ]);
    }

    public function blockAgent(Request $request, Agents $agent)
    {
        $request->validate([
            'reason' => 'required|string'
        ]);

        $agent->update([
            'is_blocked' => true,
            'blocked_reason' => $request->reason
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Agent bloqué avec succès'
        ]);
    }

    public function unblockAgent(Agents $agent)
    {
        $agent->update([
            'is_blocked' => false,
            'blocked_reason' => null
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Agent débloqué avec succès'
        ]);
    }

    public function store(Request $request)
    {
        $authAgent = auth('api_agents')->user();
        
        // Définir les roles autorisés selon le role de l'agent connecté
        $allowedRoles = ['agent']; // Par défaut, seul agent
        if ($authAgent->isSuperAdmin()) {
            $allowedRoles = ['super_admin', 'maire', 'agent']; // Seul super admin peut créer maire et super admin
        } elseif ($authAgent->isMaire()) {
            $allowedRoles = ['agent']; // Maire peut seulement créer des agents
        }
        
        $data = $request->validate([
            'commune_id' => ['required', 'exists:communes,id'],
            'nom' => 'required|string|max:255',
            'telephone' => 'required|string|max:20',
            'email' => 'required|email|unique:agents,email',
            'role' => ['required', 'in:' . implode(',', $allowedRoles)],
            'password' => 'required|string|min:6|confirmed'
        ]);

        // Si ce n'est pas un super admin, forcer la commune de l'agent connecté
        if (!$authAgent->isSuperAdmin()) {
            $data['commune_id'] = $authAgent->commune_id;
        }

        $agent = Agents::create($data);

        return response()->json([
            'status' => true,
            'message' => 'Agent créé avec succès',
            'data' => $agent->load('commune')
        ], 201);
    }

    public function show(Agents $agent)
    {
        return response()->json([
            'status' => true,
            'data' => $agent->load('commune')
        ]);
    }

 

public function update(Request $request, Agents $agent)
{
    $authAgent = auth('api_agents')->user();
    
    // Vérification : seul le super admin peut modifier des agents d'autres communes
    if (!$authAgent->isSuperAdmin() && $agent->commune_id !== $authAgent->commune_id) {
        return response()->json([
            'status' => false,
            'message' => 'Accès refusé'
        ], 403);
    }
    
    // Définir les roles autorisés selon le role de l'agent connecté
    $allowedRoles = ['agent']; // Par défaut, seul agent
    if ($authAgent->isSuperAdmin()) {
        $allowedRoles = ['super_admin', 'maire', 'agent']; // Seul super admin peut modifier vers maire et super admin
    } elseif ($authAgent->isMaire()) {
        $allowedRoles = ['agent']; // Maire peut seulement modifier des agents
    }
    
    $data = $request->validate([
        'commune_id' => ['required', 'exists:communes,id'],
        'nom' => ['required', 'string', 'max:255'],
        'telephone' => ['required', 'string', 'max:20'],

        'email' => [
            'required',
            'email',
            Rule::unique('agents', 'email')->ignore($agent->id),
        ],

        'role' => ['required', 'in:' . implode(',', $allowedRoles)],
        'password' => ['nullable', 'string', 'min:6'],
    ]);

    // Si ce n'est pas un super admin, forcer la commune de l'agent connecté
    if (!$authAgent->isSuperAdmin()) {
        $data['commune_id'] = $authAgent->commune_id;
    }

    // éviter conflit password
    if (empty($data['password'])) {
        unset($data['password']);
    }

    $agent->update($data);

    return response()->json([
        'status' => true,
        'message' => 'Agent mis à jour avec succès',
        'data' => $agent->load('commune')
    ]);
}

    public function destroy(Agents $agent)
    {
        $authAgent = auth('api_agents')->user();
        
        // Vérification : seul le super admin peut supprimer des agents d'autres communes
        if (!$authAgent->isSuperAdmin() && $agent->commune_id !== $authAgent->commune_id) {
            return response()->json([
                'status' => false,
                'message' => 'Accès refusé'
            ], 403);
        }
        
        $agent->delete();

        return response()->json([
            'status' => true,
            'message' => 'Agent supprimé avec succès'
        ]);
    }
}