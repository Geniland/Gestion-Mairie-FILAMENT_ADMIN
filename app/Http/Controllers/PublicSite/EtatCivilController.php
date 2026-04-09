<?php

namespace App\Http\Controllers\PublicSite;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;


class EtatCivilController extends Controller
{
    public function index()
    {
        if (DB::getSchemaBuilder()->hasTable('etat_civil_requests')) {
            $data = DB::table('etat_civil_requests')->orderBy('id','desc')->get();
        } else {
            $data = [];
        }
        return response()->json(['status'=>true,'data'=>$data]);
    }

    public function store(Request $request)
    {
        $v = Validator::make($request->all(), [
            'nom' => 'required|string|max:255',
            'telephone' => 'required|string|max:50',
            'email' => 'required|email',
            'type' => 'required|in:naissance,deces,mariage,rectification',
            'details' => 'nullable|string',
            'fichiers.*' => 'file|max:4096',
        ]);
        if ($v->fails()) {
            return response()->json(['status'=>false,'errors'=>$v->errors()],422);
        }

        $files = [];
        if ($request->hasFile('fichiers')) {
            foreach ($request->file('fichiers') as $file) {
                $path = $file->store('etat_civil','public');
                $files[] = $path;
            }
        }

        if (DB::getSchemaBuilder()->hasTable('etat_civil_requests')) {
            DB::table('etat_civil_requests')->insert([
                'reference' => 'ETC-'.date('YmdHis').'-'.Str::random(5),
                'nom' => $request->nom,
                'telephone' => $request->telephone,
                'email' => $request->email,
                'type' => $request->type,
                'details' => $request->details,
                'files' => json_encode($files),
                'status' => 'en_attente',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        return response()->json(['status'=>true,'message'=>'Demande enregistrée'],201);
    }

    // Placeholders pour auth publique
    // public function login(Request $request) { return response()->json(['token'=>'demo','user'=>['nom'=>'Demo User']],200); }
    // public function register(Request $request) { return response()->json(['ok'=>true],201); }
    // public function logout(Request $request) { return response()->json(['ok'=>true],200); }
    // public function forgotPassword(Request $request) { return response()->json(['ok'=>true],200); }


      // ✅ REGISTER
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6|confirmed',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        // Token Sanctum
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'status' => true,
            'message' => 'Compte créé avec succès',
            'token' => $token,
            'user' => $user
        ], 201);
    }

    // ✅ LOGIN
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Email ou mot de passe incorrect.'],
            ]);
        }

        // supprimer les anciens tokens (optionnel)
        $user->tokens()->delete();

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'status' => true,
            'message' => 'Connexion réussie',
            'token' => $token,
            'user' => $user
        ], 200);
    }

    // ✅ LOGOUT
    public function logout(Request $request)
    {
        $user = $request->user();

        if ($user) {
            $user->currentAccessToken()->delete();
        }

        return response()->json([
            'status' => true,
            'message' => 'Déconnexion réussie'
        ], 200);
    }

    // ✅ FORGOT PASSWORD
    public function forgotPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $status = Password::sendResetLink([
            'email' => $request->email
        ]);

        if ($status === Password::RESET_LINK_SENT) {
            return response()->json([
                'status' => true,
                'message' => 'Lien de réinitialisation envoyé.'
            ], 200);
        }

        return response()->json([
            'status' => false,
            'message' => 'Impossible d’envoyer le lien. Vérifiez votre email.'
        ], 400);
    }
}

