<?php

namespace App\Http\Controllers\PublicSite;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ContentController extends Controller
{
    public function services()
    {
        if (DB::getSchemaBuilder()->hasTable('services')) {
            $data = DB::table('services')->select('id','titre','description','icon')->orderBy('id','desc')->get();
        } else {
            $data = [
                ['id'=>1,'titre'=>'État civil','description'=>'Demandes d’actes','icon'=>'fa-file'],
                ['id'=>2,'titre'=>'Paiement taxes','description'=>'Régler vos taxes en ligne','icon'=>'fa-money-bill'],
            ];
        }
        return response()->json(['status'=>true,'data'=>$data]);
    }

    public function actualites()
    {
        if (DB::getSchemaBuilder()->hasTable('actualites')) {
            $data = DB::table('actualites')->select('id','titre','resume','image','published_at')->orderBy('published_at','desc')->get();
        } else {
            $data = [
                ['id'=>1,'titre'=>'Info mairie','resume'=>'Ouverture guichet unique','image'=>null,'published_at'=>now()->toDateString()],
            ];
        }
        return response()->json(['status'=>true,'data'=>$data]);
    }

    public function actualite($id)
    {
        if (DB::getSchemaBuilder()->hasTable('actualites')) {
            $item = DB::table('actualites')->where('id',$id)->first();
            if (!$item) {
                return response()->json(['status'=>false,'message'=>'Introuvable'],404);
            }
            $item->contenu = $item->contenu ?? '';
            return response()->json(['status'=>true,'data'=>$item]);
        }
        return response()->json(['status'=>false,'message'=>'Introuvable'],404);
    }
}

