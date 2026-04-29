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
        $agents = Agents::with('commune')->paginate(15);

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
        $data = $request->validate([
            'commune_id' => 'required|exists:communes,id',
            'nom' => 'required|string|max:255',
            'telephone' => 'required|string|max:20',
            'email' => 'required|email|unique:agents,email',
            'role' => 'required|in:super_admin,maire,agent',
            'password' => 'required|string|min:6|confirmed'
        ]);

        // 🔥 HASH PASSWORD PROPRE (NE DÉPEND PAS DU MODEL)
        $data['password'] = Hash::make($data['password']);

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
    $data = $request->validate([
        'commune_id' => ['required', 'exists:communes,id'],
        'nom' => ['required', 'string', 'max:255'],
        'telephone' => ['required', 'string', 'max:20'],

        'email' => [
            'required',
            'email',
            Rule::unique('agents', 'email')->ignore($agent->id),
        ],

        'role' => ['required', 'in:agent,maire,super_admin'],
        'password' => ['nullable', 'string', 'min:6'],
    ]);

    // éviter conflit password
    if (empty($data['password'])) {
        unset($data['password']);
    } else {
        $data['password'] = bcrypt($data['password']);
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
        $agent->delete();

        return response()->json([
            'status' => true,
            'message' => 'Agent supprimé avec succès'
        ]);
    }
}