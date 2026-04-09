<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PaymentsController extends Controller
{
    public function index()
    {
        return response()->json([]);
    }

    public function initiate(Request $request)
    {
        $request->validate([
            'taxe_id' => 'required',
        ]);

        // Ici on génère l'URL FedaPay normalement
        return response()->json([
            'status' => true,
            'checkout_url' => 'https://checkout.fedapay.com/placeholder'
        ]);
    }

    public function callback(Request $request)
    {
        // Logique de validation du paiement après redirection FedaPay
        return response()->json(['status' => true]);
    }

    // --- Admin Methods ---

    public function adminIndex()
    {
        // Retourne les transactions FedaPay réussies qui attendent validation admin
        return response()->json([
            'data' => [
                [
                    'id' => 1,
                    'transaction_id' => 'FP_987654321',
                    'user' => ['name' => 'Kodjo Agbegniadan'],
                    'amount' => 5000,
                    'status' => 'success',
                    'created_at' => now()->toDateTimeString()
                ]
            ]
        ]);
    }

    public function adminValidate($id)
    {
        return response()->json(['status' => true, 'message' => 'Paiement validé avec succès']);
    }
}
