<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\PublicPayment;
use App\Models\PublicTaxe;
use App\Models\Contribuable;
use App\Models\Taxe;
use App\Models\Payement;
use App\Models\Tickets;
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
            'commune_id' => $taxe->commune_id,
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
                    
                    // Mettre à jour la taxe associée et créer le ticket
                    $taxePublic = PublicTaxe::with(['user'])->find($payment->public_taxe_id);
                    if ($taxePublic) {
                        // Si pas déjà de ticket, on le crée
                        if (!$taxePublic->ticket_id) {
                            $user = $taxePublic->user;
                            
                            // Déterminer la commune à utiliser
                            $communeId = $taxePublic->commune_id;
                            
                            if (!$communeId && $user && $user->commune_id) {
                                $communeId = $user->commune_id;
                            }
                            
                            // Si toujours pas de commune, prendre la première disponible
                            if (!$communeId) {
                                $communeId = \App\Models\Commune::first()?->id;
                            }
                            
                            Log::info('Commune utilisée pour la génération', [
                                'public_taxe_id' => $taxePublic->id,
                                'commune_id' => $communeId
                            ]);
                            
                            if (!$communeId) {
                                Log::error('Aucune commune disponible pour générer le ticket');
                                $paymentStatus = 'error';
                                break;
                            }
                            
                            // 1. Créer un Contribuable si n'existe pas (basé sur le nom)
                            $contribuable = Contribuable::where('nom', $taxePublic->contribuable_nom)
                                ->where('commune_id', $communeId)
                                ->first();
                            
                            if (!$contribuable) {
                                $contribuable = Contribuable::create([
                                    'commune_id' => $communeId,
                                    'nom' => $taxePublic->contribuable_nom,
                                    'telephone' => $user?->phone ?? 'Non renseigné',
                                    'type' => 'particulier',
                                    'adresse' => 'Non renseignée'
                                ]);
                            }

                            // Trouver un agent (premier agent disponible)
                            $agentId = \App\Models\Agents::first()?->id ?? 1;
                            
                            // Trouver un quartier (premier quartier disponible)
                            $quartierId = \App\Models\Quartier::first()?->id ?? 1;

                            // 2. Créer une Taxe officielle
                            $taxe = Taxe::create([
                                'commune_id' => $communeId,
                                'contribuable_id' => $contribuable->id,
                                'type_taxe_id' => $taxePublic->type_taxe_id,
                                'agent_id' => $agentId,
                                'montant' => $taxePublic->montant,
                                'periode_debut' => $taxePublic->periode_debut,
                                'periode_fin' => $taxePublic->periode_fin,
                                'statut' => 'payee'
                            ]);

                            // 3. Créer un Payement officiel
                            Payement::create([
                                'taxe_id' => $taxe->id,
                                'agent_id' => $agentId,
                                'commune_id' => $communeId,
                                'contribuable_id' => $contribuable->id,
                                'montant' => $taxePublic->montant,
                                'mode_payement' => 'FedaPay',
                                'date_payement' => now(),
                                'quartier_id' => $quartierId,
                                'reference_transaction' => $payment->transaction_id,
                                'reference' => $payment->reference
                            ]);

                            // 4. Générer un Ticket
                            $ticket = Tickets::create([
                                'commune_id' => $communeId,
                                'contribuable_id' => $contribuable->id,
                                'taxe_id' => $taxe->id,
                                'agent_id' => $agentId,
                                'date_expiration' => now()->addYear(),
                                'statut' => 'payé',
                                'printed' => false
                            ]);

                            // 5. Mettre à jour la PublicTaxe avec la référence du ticket
                            $taxePublic->update([
                                'status' => 'payee',
                                'ticket_id' => $ticket->id,
                                'commune_id' => $communeId // Assurer que la commune est bien enregistrée
                            ]);
                        } else {
                            $taxePublic->update(['status' => 'payee']);
                        }
                        
                        Log::info('Taxe mise à jour et ticket généré', [
                            'taxe_id' => $taxePublic->id, 
                            'status' => 'payee',
                            'ticket_id' => $taxePublic->ticket_id
                        ]);
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

    public function adminIndex()
    {
        try {
            $agent = auth()->user();
            $query = PublicPayment::with(['user', 'taxe.typeTaxe']);

            if (!$agent->isSuperAdmin()) {
                $query->where('commune_id', $agent->commune_id);
            }

            $payments = $query->orderBy('created_at', 'desc')->get();

            $data = $payments->map(function($item) {
                return [
                    'id' => $item->id,
                    'user_id' => $item->user_id,
                    'user_name' => $item->user ? $item->user->name : 'Citoyen #' . $item->user_id,
                    'taxe_id' => $item->public_taxe_id,
                    'taxe_reference' => $item->taxe ? $item->taxe->reference : null,
                    'montant' => $item->montant,
                    'reference' => $item->reference,
                    'status' => $item->status,
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

    public function adminValidate(Request $request, $id)
    {
        $payment = PublicPayment::with(['taxe', 'user'])->findOrFail($id);
        $admin = auth()->user();
        $taxePublic = $payment->taxe;
        $user = $payment->user;

        $payment->update([
            'status' => 'validé'
        ]);

        // Déterminer la commune à utiliser
        $communeId = $taxePublic->commune_id;
        
        if (!$communeId && $user && $user->commune_id) {
            $communeId = $user->commune_id;
        }
        
        if (!$communeId && $admin && $admin->commune_id) {
            $communeId = $admin->commune_id;
        }
        
        // Si toujours pas de commune, prendre la première disponible
        if (!$communeId) {
            $communeId = \App\Models\Commune::first()?->id;
        }
        
        if (!$communeId) {
            return response()->json([
                'status' => false,
                'message' => 'Aucune commune disponible pour générer le ticket'
            ], 400);
        }

        // 1. Créer un Contribuable si n'existe pas (basé sur le nom)
        $contribuable = Contribuable::where('nom', $taxePublic->contribuable_nom)
            ->where('commune_id', $communeId)
            ->first();
        
        if (!$contribuable) {
            $contribuable = Contribuable::create([
                'commune_id' => $communeId,
                'nom' => $taxePublic->contribuable_nom,
                'telephone' => $user?->phone ?? 'Non renseigné',
                'type' => 'particulier',
                'adresse' => 'Non renseignée'
            ]);
        }

        // Trouver un quartier (premier quartier disponible)
        $quartierId = \App\Models\Quartier::first()?->id ?? 1;

        // 2. Créer une Taxe officielle
        $taxe = Taxe::create([
            'commune_id' => $communeId,
            'contribuable_id' => $contribuable->id,
            'type_taxe_id' => $taxePublic->type_taxe_id,
            'agent_id' => $admin->id,
            'montant' => $taxePublic->montant,
            'periode_debut' => $taxePublic->periode_debut,
            'periode_fin' => $taxePublic->periode_fin,
            'statut' => 'payee'
        ]);

        // 3. Créer un Payement officiel
        $payement = Payement::create([
            'taxe_id' => $taxe->id,
            'agent_id' => $admin->id,
            'commune_id' => $communeId,
            'contribuable_id' => $contribuable->id,
            'montant' => $taxePublic->montant,
            'mode_payement' => 'FedaPay',
            'date_payement' => now(),
            'quartier_id' => $quartierId,
            'reference_transaction' => $payment->transaction_id,
            'reference' => $payment->reference
        ]);

        // 4. Générer un Ticket
        $ticket = Tickets::create([
            'commune_id' => $communeId,
            'contribuable_id' => $contribuable->id,
            'taxe_id' => $taxe->id,
            'agent_id' => $admin->id,
            'date_expiration' => now()->addYear(),
            'statut' => 'payé',
            'printed' => false
        ]);

        // 5. Mettre à jour la PublicTaxe avec la référence du ticket
        $taxePublic->update([
            'status' => 'payee',
            'ticket_id' => $ticket->id,
            'commune_id' => $communeId // Assurer que la commune est bien enregistrée
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Paiement validé avec succès. Ticket généré.',
            'ticket_id' => $ticket->id,
            'ticket_number' => $ticket->numero_ticket
        ]);
    }
}