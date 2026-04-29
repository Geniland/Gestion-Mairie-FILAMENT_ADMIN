<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\EtatCivilRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;


use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;

class EtatCivilController extends Controller
{
    /**
     * ✅ LOGIN PUBLIC (CITOYEN)
     * Utilisation d'une validation manuelle pour éviter les conflits de guards session/api
     */
    public function login(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $validated['email'])->first();

        if ($user && Hash::check($validated['password'], $user->password)) {
            if ($user->is_blocked) {
                return response()->json([
                    'status' => false,
                    'message' => 'Votre compte est bloqué. Raison : ' . ($user->blocked_reason ?? 'Non spécifiée')
                ], 403);
            }

            $token = $user->createToken('public-token')->plainTextToken;

            return response()->json([
                'status' => true,
                'message' => 'Connexion réussie',
                'token' => $token,
                'user' => $user
            ], 200);
        }

        return response()->json([
            'status' => false,
            'message' => 'Identifiants invalides'
        ], 401);
    }

    /**
     * ✅ REGISTER PUBLIC
     */
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'phone' => 'required|string|max:20|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);

        // Le password sera automatiquement hashé grâce au cast 'hashed' dans le model User
        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'password' => $validated['password'],
        ]);

        $token = $user->createToken('public-token')->plainTextToken;

        return response()->json([
            'status' => true,
            'message' => 'Utilisateur créé avec succès',
            'token' => $token,
            'user' => $user
        ], 201);
    }

    /**
     * ✅ LOGOUT PUBLIC
     */
    public function logout(Request $request)
    {
        if ($request->user()) {
            $request->user()->currentAccessToken()->delete();
        }
        
        return response()->json([
            'status' => true,
            'message' => 'Déconnexion réussie'
        ], 200);
    }

    /**
     * ✅ FORGOT PASSWORD
     */
    // public function forgotPassword(Request $request)
    // {
    //     $request->validate(['email' => 'required|email|exists:users,email']);
    //     return response()->json(['status' => true, 'message' => 'Email de réinitialisation envoyé']);
    // }

    public function forgotPassword(Request $request)
{
    $request->validate([
        'email' => 'required|email',
    ]);

    $status = Password::sendResetLink(
        $request->only('email')
    );

    // Toujours retourner la même réponse (sécurité)
    return response()->json([
        'status' => true,
        'message' => 'Si un compte existe avec cette adresse email, un lien de réinitialisation a été envoyé.'
    ]);
}

    /**
     * ✅ LISTE (PUBLIC - Propre historique)
     */
    public function index(Request $request)
    {
        $user = Auth::guard('sanctum')->user();
        if (!$user) return response()->json(['status' => false, 'message' => 'Non authentifié'], 401);

        $historique = EtatCivilRequest::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();
        
        return response()->json(['status' => true, 'data' => $historique]);
    }

    /**
     * ✅ HISTORIQUE (Alias de index pour compatibilité)
     */
    public function historique(Request $request)
    {
        return $this->index($request);
    }

    /**
     * ✅ CREATION DEMANDE (PUBLIC)
     */
    public function store(Request $request)
    {
        $user = Auth::guard('sanctum')->user();
        if (!$user) return response()->json(['status' => false, 'message' => 'Non authentifié'], 401);

        $validated = $request->validate([
            'nom' => 'required|string|max:255',
            'telephone' => 'required|string|max:50',
            'email' => 'required|email|max:255',
            'type' => 'required|in:naissance,deces,mariage,rectification',
            'details' => 'nullable|string',
            'files.*' => 'nullable|file|max:4096'
        ]);

        $files = [];
        if ($request->hasFile('files')) {
            foreach ($request->file('files') as $file) {
                $path = $file->store('etat_civil', 'public');
                $files[] = $path;
            }
        }

        $reference = 'ETC-' . date('YmdHis') . '-' . strtoupper(Str::random(5));

        $created = EtatCivilRequest::create([
            'user_id' => $user->id,
            'reference' => $reference,
            'nom' => $validated['nom'],
            'telephone' => $validated['telephone'],
            'email' => $validated['email'],
            'type' => $validated['type'],
            'details' => $validated['details'] ?? null,
            'files' => $files,
            'status' => 'en_attente',
        ]);

        return response()->json(['status' => true, 'message' => 'Demande enregistrée avec succès', 'data' => $created], 201);
    }

    /**
     * ✅ LISTE TOUTES LES DEMANDES (ADMIN)
     */
    public function adminIndex()
    {
        try {
            $demandes = EtatCivilRequest::with('user')->orderBy('created_at', 'desc')->get();
            
            // On s'assure de renvoyer une structure de données plate et propre
            $data = $demandes->map(function($item) {
                return [
                    'id' => $item->id,
                    'user_id' => $item->user_id,
                    'user_name' => $item->user ? $item->user->name : 'Citoyen #' . $item->user_id,
                    'user_email' => $item->user ? $item->user->email : $item->email,
                    'reference' => $item->reference,
                    'nom' => $item->nom,
                    'telephone' => $item->telephone,
                    'email' => $item->email,
                    'type' => $item->type,
                    'details' => $item->details,
                    'files' => is_array($item->files) ? $item->files : json_decode($item->files, true) ?? [],
                    'status' => $item->status,
                    'document_url' => $item->document_url,
                    'commentaire_admin' => $item->commentaire_admin,
                    'created_at' => $item->created_at,
                ];
            });

            return response()->json([
                'status' => true,
                'data' => $data
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false, 
                'message' => 'Erreur serveur: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * ✅ APPROUVER (ADMIN)
     */
    public function adminApprove(Request $request, $id)
    {
        $requestData = EtatCivilRequest::find($id);
        if (!$requestData) return response()->json(['status' => false, 'message' => 'Demande introuvable'], 404);

        $requestData->update([
            'status' => 'validé',
            'commentaire_admin' => $request->commentaire_admin,
            'updated_at' => now()
        ]);

        return response()->json(['status' => true, 'message' => 'Demande approuvée avec succès']);
    }

    /**
     * ✅ REJETER (ADMIN)
     */
    public function adminReject(Request $request, $id)
    {
        $requestData = EtatCivilRequest::find($id);
        if (!$requestData) return response()->json(['status' => false, 'message' => 'Demande introuvable'], 404);

        $requestData->update([
            'status' => 'rejeté',
            'commentaire_admin' => $request->commentaire_admin,
            'updated_at' => now()
        ]);

        return response()->json(['status' => true, 'message' => 'Demande rejetée avec succès']);
    }

    /**
     * ✅ UPLOAD DOCUMENT FINAL (ADMIN)
     */
    public function uploadDocument(Request $request, $id)
    {
        $requestData = EtatCivilRequest::find($id);
        if (!$requestData) return response()->json(['status' => false, 'message' => 'Demande introuvable'], 404);

        $request->validate(['document' => 'required|file|max:4096']);

        if ($request->hasFile('document')) {
            $path = $request->file('document')->store('etat_civil_final', 'public');
            $url = asset('storage/' . $path);
            $requestData->update(['document_url' => $url]);
            return response()->json(['status' => true, 'message' => 'Document uploadé', 'document_url' => $url]);
        }

        return response()->json(['status' => false, 'message' => 'Aucun fichier'], 400);
    }
}
