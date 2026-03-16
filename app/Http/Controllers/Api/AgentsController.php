<?php

namespace App\Http\Controllers\Api;

use App\Models\Agents;
use App\Models\Commune;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\Controller;

class AgentsController extends Controller
{
    /**
     * Afficher la liste des agents
     */
    public function index()
    {
        // $agents = Agents::with('commune')->paginate(15);
        // return view('agents.index', compact('agents'));
         $agents = Agents::with('commune')->paginate(15);

        return response()->json([
            'status' => true,
            'data' => $agents
        ]);
    }

    /**
     * Afficher le formulaire de création
     */
    public function create()
    {
        $communes = Commune::all();
        $roles = ['super_admin', 'maire', 'agent'];
        return view('agents.create', compact('communes', 'roles'));
    }

    /**
     * Enregistrer un nouvel agent
     */
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

        // Le password sera automatiquement hashé grâce au mutator dans le model
        Agents::create($data);

        return redirect()->route('agents.index')->with('success', 'Agent créé avec succès.');
    }

    /**
     * Afficher un agent spécifique
     */
    public function show(Agents $agent)
    {
        return view('agents.show', compact('agent'));
    }

    /**
     * Afficher le formulaire d'édition
     */
    public function edit(Agents $agent)
    {
        $communes = Commune::all();
        $roles = ['super_admin', 'maire', 'agent'];
        return view('agents.edit', compact('agent', 'communes', 'roles'));
    }

    /**
     * Mettre à jour un agent
     */
    public function update(Request $request, Agents $agent)
    {
        $data = $request->validate([
            'commune_id' => 'required|exists:communes,id',
            'nom' => 'required|string|max:255',
            'telephone' => 'required|string|max:20',
            'email' => 'required|email|unique:agents,email,' . $agent->id,
            'role' => 'required|in:super_admin,maire,agent',
            'password' => 'nullable|string|min:6|confirmed'
        ]);

        // Si password est vide, on ne change pas le mot de passe
        if (empty($data['password'])) {
            unset($data['password']);
        }

        $agent->update($data);

        return redirect()->route('agents.index')->with('success', 'Agent mis à jour avec succès.');
    }

    /**
     * Supprimer un agent
     */
    public function destroy(Agents $agent)
    {
        $agent->delete();
        return redirect()->route('agents.index')->with('success', 'Agent supprimé avec succès.');
    }
}
