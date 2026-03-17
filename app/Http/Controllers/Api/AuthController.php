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
}