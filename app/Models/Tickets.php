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
        'statut'
    ];

    protected static function booted()
    {
        static::creating(function ($ticket) {
            $ticket->qr_hash = Str::uuid();
        });
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($ticket) {

            $ticket->numero_ticket =
                'TCK-'.date('Y').'-'.rand(100000,999999);

            $ticket->qr_hash =
                md5($ticket->numero_ticket.time());
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