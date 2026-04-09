<?php

namespace App\Http\Controllers\PublicSite;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Taxe;
use App\Models\Contribuable;

class TaxesController extends Controller
{
    public function store(Request $request)
    {
        $v = Validator::make($request->all(), [
            'contribuable_nom' => 'required|string|max:255',
            'type_taxe_id' => 'required|exists:types_taxes,id',
            'montant' => 'required|numeric|min:0',
            'periode_debut' => 'required|date',
            'periode_fin' => 'required|date|after_or_equal:periode_debut',
        ]);
        if ($v->fails()) {
            return response()->json(['status'=>false,'errors'=>$v->errors()],422);
        }

        // Créer un contribuable "public" minimal si nécessaire
        $contribuable = Contribuable::firstOrCreate(
            ['nom' => $request->contribuable_nom, 'telephone' => $request->input('telephone','')],
            ['type' => 'particulier', 'commune_id' => $request->input('commune_id', 1), 'adresse' => $request->input('adresse','')]
        );

        $taxe = Taxe::create([
            'commune_id' => $contribuable->commune_id,
            'contribuable_id' => $contribuable->id,
            'type_taxe_id' => $request->type_taxe_id,
            'montant' => $request->montant,
            'periode_debut' => $request->periode_debut,
            'periode_fin' => $request->periode_fin,
            'statut' => 'impayee'
        ]);

        return response()->json(['status'=>true,'message'=>'Taxe créée','data'=>$taxe],201);
    }
}

