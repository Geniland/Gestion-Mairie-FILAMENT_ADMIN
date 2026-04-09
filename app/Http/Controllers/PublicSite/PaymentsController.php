<?php

namespace App\Http\Controllers\PublicSite;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PaymentsController extends Controller
{
    public function index()
    {
        if (DB::getSchemaBuilder()->hasTable('online_transactions')) {
            $data = DB::table('online_transactions')->orderBy('id','desc')->get();
        } else {
            $data = [];
        }
        return response()->json(['status'=>true,'data'=>$data]);
    }

    public function initiate(Request $request)
    {
        $request->validate(['taxe_id'=>'required|exists:taxes,id']);

        $ref = 'TRX-'.date('YmdHis').'-'.Str::random(6);

        if (DB::getSchemaBuilder()->hasTable('online_transactions')) {
            DB::table('online_transactions')->insert([
                'taxe_id' => $request->taxe_id,
                'reference' => $ref,
                'amount' => DB::table('taxes')->where('id',$request->taxe_id)->value('montant') ?? 0,
                'status' => 'en_attente',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Intégration FedaPay à brancher ici (utiliser env() pour les clés)
        $checkoutUrl = url('/')."/public/checkout/mock?ref={$ref}";

        return response()->json(['status'=>true,'checkout_url'=>$checkoutUrl,'reference'=>$ref]);
    }

    public function callback(Request $request)
    {
        $reference = $request->input('reference');
        $status = $request->input('status','en_attente');
        if (DB::getSchemaBuilder()->hasTable('online_transactions')) {
            DB::table('online_transactions')->where('reference',$reference)->update([
                'status' => $status,
                'updated_at' => now(),
            ]);
        }
        return response()->json(['status'=>true]);
    }
}

