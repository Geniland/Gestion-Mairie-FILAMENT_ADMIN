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

class EtatCivilController extends Controller
{
    // ✅ LISTE (ADMIN)
    public function index()
    {
        $requests = EtatCivilRequest::with('user')
            ->orderBy('id', 'desc')
            ->paginate(15);

        return response()->json([
            'status' => true,
            'message' => 'Liste des demandes état civil',
            'data' => $requests
        ], 200);
    }

    // ✅ DETAIL
    public function show($id)
    {
        $requestData = EtatCivilRequest::with('user')->find($id);

        if (!$requestData) {
            return response()->json([
                'status' => false,
                'message' => 'Demande introuvable'
            ], 404);
        }

        return response()->json([
            'status' => true,
            'data' => $requestData
        ], 200);
    }

    // ✅ CREATION (PUBLIC)
    public function store(Request $request)
    {
        $user = auth('sanctum')->user();
        if (!$user) {
            return response()->json(['status' => false, 'message' => 'Non authentifié'], 401);
        }

        $validated = $request->validate([
            'nom' => 'required|string|max:255',
            'telephone' => 'required|string|max:50',
            'email' => 'required|email|max:255',
            'type' => 'required|in:naissance,deces,mariage,rectification',
            'details' => 'nullable|string',
            'files.*' => 'nullable|file|max:4096'
        ]);

        // upload fichiers
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

        return response()->json([
            'status' => true,
            'message' => 'Demande enregistrée avec succès',
            'data' => $created
        ], 201);
    }

    // ✅ UPDATE (ADMIN)
    public function update(Request $request, $id)
    {
        $requestData = DB::table('etat_civil_requests')->where('id', $id)->first();

        if (!$requestData) {
            return response()->json([
                'status' => false,
                'message' => 'Demande introuvable'
            ], 404);
        }

        $validated = $request->validate([
            'nom' => 'nullable|string|max:255',
            'telephone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'type' => 'nullable|in:naissance,deces,mariage,rectification',
            'details' => 'nullable|string',
        ]);

        // Supprimer les champs null
        $data = array_filter($validated, function ($value) {
            return !is_null($value);
        });

        $data['updated_at'] = now();

        DB::table('etat_civil_requests')->where('id', $id)->update($data);

        $updated = DB::table('etat_civil_requests')->where('id', $id)->first();

        return response()->json([
            'status' => true,
            'message' => 'Demande mise à jour',
            'data' => $updated
        ], 200);
    }

    // ✅ VALIDATION (ADMIN)
    public function approve(Request $request, $id)
    {
        $requestData = EtatCivilRequest::find($id);

        if (!$requestData) {
            return response()->json([
                'status' => false,
                'message' => 'Demande introuvable'
            ], 404);
        }

        $requestData->update([
            'status' => 'validé',
            'commentaire_admin' => $request->commentaire_admin
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Demande validée'
        ], 200);
    }

    // ✅ REJET (ADMIN)
    public function reject(Request $request, $id)
    {
        $requestData = EtatCivilRequest::find($id);

        if (!$requestData) {
            return response()->json([
                'status' => false,
                'message' => 'Demande introuvable'
            ], 404);
        }

        $requestData->update([
            'status' => 'rejeté',
            'commentaire_admin' => $request->commentaire_admin
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Demande rejetée'
        ], 200);
    }

    // ✅ DELETE
    public function destroy($id)
    {
        $requestData = DB::table('etat_civil_requests')->where('id', $id)->first();

        if (!$requestData) {
            return response()->json([
                'status' => false,
                'message' => 'Demande introuvable'
            ], 404);
        }

        DB::table('etat_civil_requests')->where('id', $id)->delete();

        return response()->json([
            'status' => true,
            'message' => 'Demande supprimée'
        ], 200);
    }

    // ✅ HISTORIQUE UTILISATEUR
    public function historique(Request $request)
    {
        $user = Auth::guard('sanctum')->user();
        
        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'Non authentifié'
            ], 401);
        }
        
        $historique = EtatCivilRequest::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();
        
        return response()->json([
            'status' => true,
            'data' => $historique
        ]);
    }

    // ✅ LOGIN
    public function login(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        if (Auth::guard('users')->attempt($validated)) {

            $user = Auth::guard('users')->user();

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

    // ✅ REGISTER
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        $token = $user->createToken('public-token')->plainTextToken;

        return response()->json([
            'status' => true,
            'message' => 'Utilisateur créé avec succès',
            'token' => $token,
            'user' => $user
        ], 201);
    }

    // ✅ LOGOUT
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

    // ✅ MOT DE PASSE OUBLIÉ
    public function forgotPassword(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email|exists:users,email'
        ]);

        // TODO: Implémenter l'envoi d'email de réinitialisation
        return response()->json([
            'status' => true,
            'message' => 'Email de réinitialisation envoyé'
        ], 200);
    }

    // ✅ ADMIN - LISTE TOUTES LES DEMANDES
    public function adminIndex()
    {
        try {
            // On récupère les demandes avec l'utilisateur associé s'il existe
            $demandes = EtatCivilRequest::with('user')
                ->orderBy('created_at', 'desc')
                ->get();
            
            // On s'assure que le résultat est bien sérialisé
            return response()->json([
                'status' => true,
                'data' => $demandes->map(function($item) {
                    return [
                        'id' => $item->id,
                        'user_id' => $item->user_id,
                        'user' => $item->user ? [
                            'id' => $item->user->id,
                            'name' => $item->user->name,
                            'email' => $item->user->email,
                        ] : null,
                        'reference' => $item->reference,
                        'nom' => $item->nom,
                        'telephone' => $item->telephone,
                        'email' => $item->email,
                        'type' => $item->type,
                        'details' => $item->details,
                        'files' => $item->files,
                        'status' => $item->status,
                        'document_url' => $item->document_url,
                        'commentaire_admin' => $item->commentaire_admin,
                        'created_at' => $item->created_at,
                        'updated_at' => $item->updated_at,
                    ];
                })
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Erreur lors de la récupération des demandes : ' . $e->getMessage()
            ], 500);
        }
    }

    // ✅ ADMIN - APPROUVER UNE DEMANDE
    public function adminApprove(Request $request, $id)
    {
        try {
            $requestData = EtatCivilRequest::find($id);

            if (!$requestData) {
                return response()->json([
                    'status' => false,
                    'message' => 'Demande introuvable'
                ], 404);
            }

            $requestData->update([
                'status' => 'validé',
                'commentaire_admin' => $request->commentaire_admin,
                'updated_at' => now()
            ]);

            return response()->json([
                'status' => true,
                'message' => 'Demande approuvée avec succès',
                'data' => $requestData
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Erreur lors de l\'approbation : ' . $e->getMessage()
            ], 500);
        }
    }

    // ✅ ADMIN - REJETER UNE DEMANDE
    public function adminReject(Request $request, $id)
    {
        try {
            $requestData = EtatCivilRequest::find($id);

            if (!$requestData) {
                return response()->json([
                    'status' => false,
                    'message' => 'Demande introuvable'
                ], 404);
            }

            $requestData->update([
                'status' => 'rejeté',
                'commentaire_admin' => $request->commentaire_admin,
                'updated_at' => now()
            ]);

            return response()->json([
                'status' => true,
                'message' => 'Demande rejetée avec succès',
                'data' => $requestData
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Erreur lors du rejet : ' . $e->getMessage()
            ], 500);
        }
    }

    // ✅ ADMIN - UPLOAD DOCUMENT FINAL
    public function uploadDocument(Request $request, $id)
    {
        $requestData = EtatCivilRequest::find($id);

        if (!$requestData) {
            return response()->json([
                'status' => false,
                'message' => 'Demande introuvable'
            ], 404);
        }

        $request->validate([
            'document' => 'required|file|mimes:pdf,jpg,jpeg,png|max:4096'
        ]);

        if ($request->hasFile('document')) {
            $path = $request->file('document')->store('etat_civil_final', 'public');
            $url = asset('storage/' . $path);
            
            $requestData->update([
                'document_url' => $url,
                'updated_at' => now()
            ]);

            return response()->json([
                'status' => true,
                'message' => 'Document uploadé avec succès',
                'document_url' => $url
            ]);
        }

        return response()->json([
            'status' => false,
            'message' => 'Aucun fichier reçu'
        ], 400);
    }

    // ✅ ADMIN - STATISTIQUES
    public function adminStats()
    {
        $total = DB::table('etat_civil_requests')->count();
        $enAttente = DB::table('etat_civil_requests')->where('status', 'en_attente')->count();
        $valides = DB::table('etat_civil_requests')->where('status', 'validé')->count();
        $rejetes = DB::table('etat_civil_requests')->where('status', 'rejeté')->count();
        
        $parType = DB::table('etat_civil_requests')
            ->select('type', DB::raw('count(*) as total'))
            ->groupBy('type')
            ->get();

        return response()->json([
            'status' => true,
            'data' => [
                'total' => $total,
                'en_attente' => $enAttente,
                'valides' => $valides,
                'rejetes' => $rejetes,
                'par_type' => $parType
            ]
        ]);
    }
}