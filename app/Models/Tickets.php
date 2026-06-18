<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Tickets extends Model
{
    protected $fillable = [
        'commune_id',
        'contribuable_id',
        'taxe_id',
        'agent_id',
        'numero_ticket',
        'qr_hash',
        'date_expiration',
        'statut',
        'printed',
        'printed_at'
    ];

    protected $casts = [
        'printed' => 'boolean',
        'printed_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($ticket) {
            // Log pour voir ce qui est reçu
            \Log::info('Tickets creating event', [
                'received_numero_ticket' => $ticket->numero_ticket,
                'received_qr_hash' => $ticket->qr_hash,
                'all_attributes' => $ticket->getAttributes()
            ]);

            // Générer un numéro de ticket seulement si pas fourni (pour les syncs du mobile)
            if (empty($ticket->numero_ticket)) {
                $ticket->numero_ticket = 'TCK-' . date('Y') . '-' . rand(100000, 999999);
            }
            // Générer un QR hash seulement si pas fourni
            if (empty($ticket->qr_hash)) {
                $ticket->qr_hash = md5($ticket->numero_ticket . time());
            }
        });
    }

    public function agent()
    {
        return $this->belongsTo(Agents::class, 'agent_id');
    }


    public function taxe()
    {
        return $this->belongsTo(Taxe::class);
    }

     public function contribuable()
    {
        return $this->belongsTo(Contribuable::class);
    }

     public function commune()
    {
        return $this->belongsTo(Commune::class);
    }
}