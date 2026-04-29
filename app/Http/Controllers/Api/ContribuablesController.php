<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use App\Models\Contribuable;
use App\Models\Commune;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
 use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\EtatCivilRequest;
use App\Models\PublicTaxe;
use App\Models\PublicPayment;

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
     * Mettre à jour un utilisateur du portail public
     */
    public function updatePublicUser(Request $request, $id)
    {
        $user = User::findOrFail($id);
        
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'phone' => 'required|string|max:20',
            'password' => 'nullable|string|min:8|confirmed'
        ]);

        if (!empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        $user->update($data);

        return response()->json([
            'status' => true,
            'message' => 'Utilisateur mis à jour avec succès',
            'data' => $user
        ]);
    }

    /**
     * Bloquer un utilisateur du portail public
     */
    public function blockPublicUser(Request $request, $id)
    {
        $user = User::findOrFail($id);
        $request->validate(['reason' => 'required|string']);

        $user->update([
            'is_blocked' => true,
            'blocked_reason' => $request->reason
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Utilisateur bloqué avec succès'
        ]);
    }

    /**
     * Débloquer un utilisateur du portail public
     */
    public function unblockPublicUser($id)
    {
        $user = User::findOrFail($id);
        $user->update([
            'is_blocked' => false,
            'blocked_reason' => null
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Utilisateur débloqué avec succès'
        ]);
    }

    /**
     * Historique d'un utilisateur
     */
    public function userHistory($id)
    {
        $user = User::findOrFail($id);
        
        // 1. Demandes d'état civil
        $etatCivilRequests = EtatCivilRequest::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();

        // 2. Taxes publiques (créées sur le portail public)
        $publicTaxes = PublicTaxe::where('user_id', $user->id)
            ->with('typeTaxe')
            ->orderBy('created_at', 'desc')
            ->get();

        // 3. Paiements en ligne
        $publicPayments = PublicPayment::where('user_id', $user->id)
            ->with('taxe.typeTaxe')
            ->orderBy('created_at', 'desc')
            ->get();

        // 4. Taxes et paiements terrain (via téléphone/nom)
        $taxesTerrain = \App\Models\Taxe::whereHas('contribuable', function($q) use ($user) {
            $q->where('telephone', $user->phone)->orWhere('nom', $user->name);
        })->with('typeTaxe')->get();

        $paymentsTerrain = \App\Models\Payement::whereHas('taxe.contribuable', function($q) use ($user) {
            $q->where('telephone', $user->phone)->orWhere('nom', $user->name);
        })->get();

        return response()->json([
            'status' => true,
            'data' => [
                'etat_civil' => $etatCivilRequests,
                'public_taxes' => $publicTaxes,
                'public_payments' => $publicPayments,
                'terrain_taxes' => $taxesTerrain,
                'terrain_payments' => $paymentsTerrain
            ]
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