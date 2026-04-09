<?php
namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Contribuable;
use App\Models\PublicTaxe;
use App\Models\TypeTaxe;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class TaxesController extends Controller
{
    public function index()
    {
        $user = auth('sanctum')->user();
        if (!$user) {
            return response()->json(['status' => false, 'message' => 'Non authentifié'], 401);
        }

        $taxes = PublicTaxe::with(['typeTaxe'])
            ->where('user_id', $user->id)
            ->orderBy('id', 'desc')
            ->paginate(15);

        return response()->json([
            'status' => true,
            'message' => 'Liste de vos taxes publiques',
            'data' => $taxes
        ], 200);
    }

    public function show($id)
    {
        $user = auth('sanctum')->user();
        $taxe = PublicTaxe::with(['typeTaxe'])
            ->where('user_id', $user->id)
            ->find($id);

        if (!$taxe) {
            return response()->json([
                'status' => false,
                'message' => 'Taxe introuvable ou non autorisée'
            ], 404);
        }

        return response()->json([
            'status' => true,
            'data' => $taxe
        ], 200);
    }

    public function store(Request $request)
    {
        $user = auth('sanctum')->user();
        if (!$user) {
            return response()->json(['status' => false, 'message' => 'Non authentifié'], 401);
        }

        $data = $request->validate([
            'contribuable_nom' => 'required|string|max:255',
            'type_taxe_id' => 'required|exists:types_taxes,id',
            'montant' => 'required|numeric|min:1',
            'periode_debut' => 'nullable|date',
            'periode_fin' => 'nullable|date',
        ]);

        $taxe = PublicTaxe::create([
            'user_id' => $user->id,
            'contribuable_nom' => $data['contribuable_nom'],
            'type_taxe_id' => $data['type_taxe_id'],
            'montant' => $data['montant'],
            'periode_debut' => $data['periode_debut'] ?? null,
            'periode_fin' => $data['periode_fin'] ?? null,
            'reference' => 'PUB-TAX-' . date('YmdHis') . '-' . Str::upper(Str::random(5)),
            'status' => 'en_attente',
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Taxe créée et en attente de validation admin',
            'data' => $taxe->load(['typeTaxe'])
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $taxe = PublicTaxe::find($id);

        if (!$taxe) {
            return response()->json([
                'status' => false,
                'message' => 'Taxe introuvable'
            ], 404);
        }

        $data = $request->validate([
            'montant' => 'nullable|numeric|min:1',
            'periode_debut' => 'nullable|date',
            'periode_fin' => 'nullable|date',
            'status' => 'nullable|in:en_attente,approuvee,rejetee,payee',
            'commentaire_admin' => 'nullable|string',
        ]);

        $taxe->update($data);

        return response()->json([
            'status' => true,
            'message' => 'Taxe mise à jour',
            'data' => $taxe
        ], 200);
    }

    public function destroy($id)
    {
        $taxe = PublicTaxe::find($id);

        if (!$taxe) {
            return response()->json([
                'status' => false,
                'message' => 'Taxe introuvable'
            ], 404);
        }

        $taxe->delete();

        return response()->json([
            'status' => true,
            'message' => 'Taxe supprimée'
        ], 200);
    }
}