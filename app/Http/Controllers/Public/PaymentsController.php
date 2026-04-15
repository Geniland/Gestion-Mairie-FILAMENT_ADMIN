<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\PublicPayment;
use App\Models\PublicTaxe;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

use FedaPay\FedaPay;
use FedaPay\Transaction;

class PaymentsController extends Controller
{
    /**
     * ✅ LISTE DES PAIEMENTS (CLIENT CONNECTÉ)
     */
    public function index(Request $request)
    {
        $user = auth('sanctum')->user();

        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'Non authentifié'
            ], 401);
        }

        $payments = PublicPayment::with(['taxe.typeTaxe'])
            ->where('user_id', $user->id)
            ->orderBy('id', 'desc')
            ->get();

        return response()->json([
            'status' => true,
            'data' => $payments
        ], 200);
    }

    /**
     * ✅ INITIATE PAYMENT (FedaPay)
     * Retourne checkout_url pour redirection
     */
    public function initiate(Request $request)
    {
        $user = auth('sanctum')->user();

        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'Non authentifié'
            ], 401);
        }

        $validated = $request->validate([
            'taxe_id' => 'required|exists:public_taxes,id',
        ]);

        $taxe = PublicTaxe::with('typeTaxe')->findOrFail($validated['taxe_id']);

        // ⚠️ Empêcher de payer une taxe déjà payée
        if ($taxe->status === 'payee') {
            return response()->json([
                'status' => false,
                'message' => 'Cette taxe est déjà payée'
            ], 400);
        }

        // ⚠️ Vérifier si une transaction en attente existe déjà pour cette taxe
        $existingPayment = PublicPayment::where('public_taxe_id', $taxe->id)
            ->where('user_id', $user->id)
            ->whereIn('status', ['en_attente'])
            ->latest()
            ->first();

        if ($existingPayment && $existingPayment->checkout_url) {
            return response()->json([
                'status' => true,
                'message' => 'Paiement déjà initié',
                'checkout_url' => $existingPayment->checkout_url
            ], 200);
        }

        // 1) Créer paiement en attente
        $payment = PublicPayment::create([
            'user_id' => $user->id,
            'public_taxe_id' => $taxe->id,
            'montant' => $taxe->montant,
            'reference' => 'PAY-' . date('YmdHis') . '-' . strtoupper(Str::random(6)),
            'status' => 'en_attente',
        ]);

        try {
            // 2) Config FedaPay
            FedaPay::setApiKey(env('FEDAPAY_SECRET_KEY'));
            FedaPay::setEnvironment(env('FEDAPAY_MODE', 'sandbox')); // live ou sandbox

            // 3) Créer transaction FedaPay
            $transaction = Transaction::create([
                "amount" => (float) $taxe->montant,
                "currency" => ["iso" => "XOF"],
                "description" => "Paiement Taxe : " . ($taxe->typeTaxe->nom ?? 'Taxe publique'),

                // callback (doit être accessible publiquement)
                "callback_url" => url('https://1f1a-2c0f-f0f8-855-4f00-2c6e-966f-a61a-8f66.ngrok-free.app/api/public/payments/callback'),

                "customer" => [
                    "firstname" => $user->name,
                    "email" => $user->email,
                ],

                "metadata" => [
                    "payment_reference" => $payment->reference,
                    "taxe_id" => $taxe->id,
                    "user_id" => $user->id,
                ],
            ]);

            // 4) Générer token checkout
            $token = $transaction->generateToken();

            // 5) Mettre à jour payment
            $payment->update([
                'transaction_id' => $transaction->id,
                'checkout_url' => $token->url,
            ]);

            return response()->json([
                'status' => true,
                'message' => 'Paiement initié',
                'checkout_url' => $token->url,
                'payment' => $payment
            ], 200);

        } catch (\Exception $e) {
            Log::error("Erreur initiation paiement FedaPay", [
                'error' => $e->getMessage(),
                'taxe_id' => $taxe->id,
                'user_id' => $user->id,
            ]);

            $payment->update([
                'status' => 'failed'
            ]);

            return response()->json([
                'status' => false,
                'message' => 'Erreur lors de la création de la transaction',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * ✅ CALLBACK FedaPay
     * FedaPay appelle cette route après paiement
     */
    public function callback(Request $request)
    {
        $transactionId = $request->input('id');

        if (!$transactionId) {
            return response()->json([
                'status' => false,
                'message' => 'Transaction ID manquant'
            ], 400);
        }

        $payment = PublicPayment::where('transaction_id', $transactionId)->first();

        if (!$payment) {
            return response()->json([
                'status' => false,
                'message' => 'Paiement introuvable'
            ], 404);
        }

        try {
            FedaPay::setApiKey(env('FEDAPAY_SECRET_KEY'));
            FedaPay::setEnvironment(env('FEDAPAY_MODE', 'sandbox'));

            $transaction = Transaction::retrieve($transactionId);

            // approved = paiement réussi
            if ($transaction->status === 'approved') {

                // paiement réussi mais attend validation admin
                $payment->update([
                    'status' => 'en_attente'
                ]);

                return response()->json([
                    'status' => true,
                    'message' => 'Paiement reçu, en attente validation admin'
                ], 200);
            }

            // échec / annulé
            $payment->update([
                'status' => 'failed'
            ]);

            return response()->json([
                'status' => false,
                'message' => 'Paiement non approuvé',
                'fedapay_status' => $transaction->status
            ], 200);

        } catch (\Exception $e) {
            Log::error("Erreur callback FedaPay", [
                'transaction_id' => $transactionId,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'status' => false,
                'message' => 'Erreur serveur callback'
            ], 500);
        }
    }

    /**
     * ==========================
     *       ADMIN METHODS
     * ==========================
     */

    /**
     * ✅ ADMIN LISTE DES TRANSACTIONS EN ATTENTE
     */
    public function adminIndex()
    {
        $payments = PublicPayment::with(['user', 'taxe.typeTaxe'])
            ->where('status', 'en_attente')
            ->orderBy('id', 'desc')
            ->paginate(20);

        return response()->json([
            'status' => true,
            'message' => 'Liste des paiements en attente',
            'data' => $payments
        ], 200);
    }

    /**
     * ✅ ADMIN VALIDE UN PAIEMENT
     * => passe paiement à validé
     * => passe taxe à payee
     */
    public function adminValidate($id)
    {
        $payment = PublicPayment::with('taxe')->find($id);

        if (!$payment) {
            return response()->json([
                'status' => false,
                'message' => 'Paiement introuvable'
            ], 404);
        }

        if ($payment->status === 'validé') {
            return response()->json([
                'status' => true,
                'message' => 'Paiement déjà validé'
            ], 200);
        }

        $payment->update([
            'status' => 'validé'
        ]);

        if ($payment->taxe) {
            $payment->taxe->update([
                'status' => 'payee'
            ]);
        }

        return response()->json([
            'status' => true,
            'message' => 'Paiement validé avec succès'
        ], 200);
    }

    /**
     * ✅ ADMIN REJETER UN PAIEMENT (OPTIONNEL)
     */
    public function adminReject($id)
    {
        $payment = PublicPayment::find($id);

        if (!$payment) {
            return response()->json([
                'status' => false,
                'message' => 'Paiement introuvable'
            ], 404);
        }

        $payment->update([
            'status' => 'rejeté'
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Paiement rejeté'
        ], 200);
    }
}