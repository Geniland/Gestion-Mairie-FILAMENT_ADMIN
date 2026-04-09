<?php

namespace App\Http\Controllers\Api;

use App\Models\Tickets;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class TicketsController extends Controller
{
    /**
     * Liste des tickets
     */
    public function index()
    {
        $user = auth()->user();
        
        $query = Tickets::with(['taxe', 'commune', 'contribuable', 'agent']);

        if ($user && method_exists($user, 'isAgent') && $user->isAgent()) {
            $query->where('agent_id', $user->id);
        }

        $tickets = $query->latest()->paginate(15);

        return response()->json([
            'success' => true,
            'message' => 'Liste des tickets',
            'data' => $tickets
        ]);
    }

    /**
     * Créer un nouveau ticket
     */
    public function store(Request $request)
    {
        $user = auth()->user();
        
        $data = $request->validate([
            'commune_id' => 'required|exists:communes,id',
            'contribuable_id' => 'required|exists:contribuables,id',
            'taxe_id' => 'required|exists:taxes,id',
            'date_expiration' => 'required|date',
            'statut' => 'required|string'
        ]);

        // 🔥 Éviter doublon ticket pour la même taxe
        $existing = Tickets::where('taxe_id', $data['taxe_id'])->first();
        if ($existing) {
            return response()->json([
                'success' => true,
                'message' => 'Un ticket existe déjà pour cette taxe.',
                'data' => $existing->load(['taxe.typeTaxe', 'commune', 'contribuable', 'agent'])
            ], 200);
        }

        $data['agent_id'] = $user->id;

        $ticket = Tickets::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Ticket créé avec succès',
            'data' => $ticket->load(['taxe.typeTaxe', 'commune', 'contribuable', 'agent'])
        ], 201);
    }

    /**
     * Afficher un ticket
     */
    public function show($id)
    {
        $ticket = Tickets::with(['taxe', 'commune', 'contribuable', 'agent'])->find($id);

        if (!$ticket) {
            return response()->json([
                'success' => false,
                'message' => 'Ticket non trouvé'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $ticket
        ]);
    }

    /**
     * Mettre à jour un ticket
     */
    public function update(Request $request, $id)
    {
        $ticket = Tickets::find($id);

        if (!$ticket) {
            return response()->json([
                'success' => false,
                'message' => 'Ticket non trouvé'
            ], 404);
        }

        $data = $request->validate([
            'commune_id' => 'required|exists:communes,id',
            'contribuable_id' => 'required|exists:contribuables,id',
            'taxe_id' => 'required|exists:taxes,id',
            'agent_id' => 'required|exists:agents,id',
            'date_expiration' => 'required|date',
            'statut' => 'required|string'
        ]);

        $ticket->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Ticket mis à jour avec succès',
            'data' => $ticket
        ]);
    }

    /**
     * Supprimer un ticket
     */
    public function destroy($id)
    {
        $ticket = Tickets::find($id);

        if (!$ticket) {
            return response()->json([
                'success' => false,
                'message' => 'Ticket non trouvé'
            ], 404);
        }

        $ticket->delete();

        return response()->json([
            'success' => true,
            'message' => 'Ticket supprimé avec succès'
        ]);
    }
}