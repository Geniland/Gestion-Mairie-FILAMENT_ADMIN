<?php

namespace App\Http\Controllers;

use App\Models\Tickets;
use Illuminate\Http\Request;
use Carbon\Carbon;

class PublicTicketController extends Controller
{
    public function verify($hash)
    {
        $ticket = Tickets::with(['taxe.typeTaxe', 'commune', 'contribuable'])
            ->where('qr_hash', $hash)
            ->first();

        if (!$ticket) {
            return response()->json([
                'status' => 'invalid',
                'message' => 'Ticket introuvable ou invalide.'
            ], 404);
        }

        $now = Carbon::now();
        $expiration = Carbon::parse($ticket->date_expiration);
        
        $isExpired = $now->greaterThan($expiration);
        $diffDays = $now->diffInDays($expiration, false);

        $status = 'valid';
        if ($ticket->statut === 'annule' || $ticket->statut === 'frauduleux') {
            $status = 'fraudulent';
        } elseif ($isExpired) {
            $status = 'expired';
        }

        return response()->json([
            'status' => $status,
            'ticket' => [
                'numero' => $ticket->numero_ticket,
                'contribuable' => $ticket->contribuable->nom,
                'taxe' => $ticket->taxe->typeTaxe->nom,
                'commune' => $ticket->commune->nom,
                'montant' => $ticket->taxe->montant,
                'date_expiration' => $expiration->format('d/m/Y'),
                'statut_db' => $ticket->statut,
                'is_expired' => $isExpired,
                'days_diff' => $diffDays,
            ]
        ]);
    }
}
