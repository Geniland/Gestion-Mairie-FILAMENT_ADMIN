<?php

namespace App\Http\Controllers\Api;

use App\Models\Agents;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    /**
     * Login et génération du token
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string'
        ]);

        $agent = Agents::where('email', $request->email)->first();

        if (!$agent || !Hash::check($request->password, $agent->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Identifiants invalides'
            ], 401);
        }

        if ($agent->is_blocked) {
            return response()->json([
                'success' => false,
                'message' => 'Compte bloqué',
                'reason' => $agent->blocked_reason
            ], 403);
}

        $token = $agent->createToken('api-token')->plainTextToken;

        return response()->json([
            'success' => true,
            'agent' => $agent,
            'token' => $token
        ]);
    }

    /**
     * Logout : supprimer le token
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Déconnecté avec succès'
        ]);
    }

    /**
     * Récupérer le profil de l’agent connecté
     */
    public function profile(Request $request)
    {
        return response()->json([
            'success' => true,
            'agent' => $request->user()
        ]);
    }

    /**
     * Mettre à jour le profil de l’agent connecté
     */
    public function updateProfile(Request $request)
    {
        $agent = $request->user();

        $request->validate([
            'nom' => 'required|string|max:255',
            'email' => 'required|email|unique:agents,email,' . $agent->id,
            'telephone' => 'nullable|string|max:20',
            'password' => 'nullable|string|min:6|confirmed',
        ]);

        $data = [
            'nom' => $request->nom,
            'email' => $request->email,
            'telephone' => $request->telephone,
        ];

        if ($request->filled('password')) {
            $data['password'] = $request->password;
        }

        $agent->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Profil mis à jour avec succès',
            'agent' => $agent
        ]);
    }
}