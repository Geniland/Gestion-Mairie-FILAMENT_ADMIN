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
    /* =========================
        INIT PAYMENT
    ========================= */
    public function initiate(Request $request)
    {
        $user = auth('sanctum')->user();

        if (!$user) {
            return response()->json(['status' => false, 'message' => 'Non authentifié'], 401);
        }

        $request->validate([
            'taxe_id' => 'required|exists:public_taxes,id'
        ]);

        $taxe = PublicTaxe::with('typeTaxe')->findOrFail($request->taxe_id);

        // Vérifier si la taxe est déjà payée (adapté à votre table public_taxes)
        if ($taxe->status === 'payee' || $taxe->status === 'validé') {
            return response()->json(['status' => false, 'message' => 'Taxe déjà payée'], 400);
        }

        $payment = PublicPayment::create([
            'user_id' => $user->id,
            'public_taxe_id' => $taxe->id,
            'montant' => $taxe->montant,
            'reference' => 'PAY-' . strtoupper(Str::random(10)),
            'status' => 'en_attente'
        ]);

        try {
            /* CONFIG FEDAPAY */
            FedaPay::setApiKey(env('FEDAPAY_SECRET_KEY'));
            FedaPay::setEnvironment(env('FEDAPAY_MODE', 'sandbox'));

            $frontend = rtrim(env('FRONT_URL'), '/');
            $backend  = rtrim(env('APP_URL'), '/');

            /* TRANSACTION */
            $transaction = Transaction::create([
                "amount" => (int) $taxe->montant,
                "currency" => ["iso" => "XOF"],
                "description" => "Paiement taxe " . $taxe->reference,

                /* 🔥 IMPORTANT : webhook backend */
                "callback_url" => $backend . "/api/public/payments/callback",

                /* 🔥 IMPORTANT : retour utilisateur */
                "return_url" => $frontend . "/taxes", // CORRIGÉ : /app/taxes → /taxes

                "customer" => [
                    "firstname" => $user->name,
                    "lastname" => $user->name,
                    "email" => $user->email,
                ],

                "metadata" => [
                    "payment_reference" => $payment->reference,
                    "taxe_id" => $taxe->id,
                    "user_id" => $user->id,
                ],
            ]);

            $token = $transaction->generateToken();

            $payment->update([
                'transaction_id' => $transaction->id,
                'checkout_url' => $token->url
            ]);

            return response()->json([
                'status' => true,
                'checkout_url' => $token->url
            ]);

        } catch (\Exception $e) {
            Log::error('Erreur initiation paiement: ' . $e->getMessage());

            $payment->update(['status' => 'failed']);

            return response()->json([
                'status' => false,
                'message' => 'Erreur paiement: ' . $e->getMessage()
            ], 500);
        }
    }

    /* =========================
        CALLBACK (WEBHOOK) CORRIGÉ
    ========================= */
    public function callback(Request $request)
    {
        // Log pour déboguer
        Log::info('Callback FedaPay reçu', [
            'all_inputs' => $request->all(),
            'method' => $request->method()
        ]);
        
        $transactionId = $request->input('id');

        if (!$transactionId) {
            Log::warning('ID transaction manquant dans le callback');
            return redirect('http://localhost:5173/taxes?payment_status=missing_id');
        }

        $payment = PublicPayment::where('transaction_id', $transactionId)->first();

        if (!$payment) {
            Log::warning('Paiement non trouvé', ['transaction_id' => $transactionId]);
            return redirect('http://localhost:5173/taxes?payment_status=not_found');
        }

        try {
            FedaPay::setApiKey(env('FEDAPAY_SECRET_KEY'));
            FedaPay::setEnvironment(env('FEDAPAY_MODE', 'sandbox'));

            $transaction = Transaction::retrieve($transactionId);
            
            Log::info('Transaction FedaPay récupérée', [
                'id' => $transaction->id,
                'status' => $transaction->status,
                'amount' => $transaction->amount
            ]);

            $paymentStatus = '';
            
            /* =========================
                STATUTS FEDAPAY → STATUTS TABLE
            ========================= */
            switch ($transaction->status) {
                case 'approved':
                    // ✅ Utiliser 'validé' au lieu de 'payee' car c'est ce qui est dans votre ENUM
                    $payment->update(['status' => 'validé']);
                    $paymentStatus = 'success';
                    
                    // Mettre à jour la taxe associée
                    $taxe = PublicTaxe::find($payment->public_taxe_id);
                    if ($taxe) {
                        $taxe->update(['status' => 'payee']);
                        Log::info('Taxe mise à jour', ['taxe_id' => $taxe->id, 'status' => 'payee']);
                    }
                    break;

                case 'canceled':
                case 'declined':
                    // ✅ Utiliser 'rejeté' pour les annulations
                    $payment->update(['status' => 'rejeté']);
                    $paymentStatus = 'failed';
                    break;
                    
                case 'failed':
                    // ✅ Utiliser 'failed' qui existe dans votre ENUM
                    $payment->update(['status' => 'failed']);
                    $paymentStatus = 'failed';
                    break;

                case 'pending':
                default:
                    // ✅ Garder 'en_attente'
                    $payment->update(['status' => 'en_attente']);
                    $paymentStatus = 'pending';
                    break;
            }
            
            Log::info('Paiement mis à jour avec succès', [
                'payment_id' => $payment->id,
                'new_status' => $paymentStatus,
                'db_status' => $payment->status
            ]);

            // Redirection vers Vue.js avec le statut du paiement
            return redirect("http://localhost:5173/taxes?payment_status={$paymentStatus}&transaction_id={$transactionId}");

        } catch (\Exception $e) {
            Log::error('Erreur dans le callback FedaPay', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            // Tentative de mise à jour en failed
            try {
                if (isset($payment)) {
                    $payment->update(['status' => 'failed']);
                }
            } catch (\Exception $inner) {
                Log::error('Impossible de mettre à jour le statut', ['error' => $inner->getMessage()]);
            }
            
            return redirect('http://localhost:5173/taxes?payment_status=error');
        }
    }
}