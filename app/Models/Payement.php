<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payement extends Model
{
   protected $fillable = [
        'taxe_id',
        'agent_id',
        'commune_id',
        'contribuable_id',
        'montant',
        'mode_payement',
        'date_payement',
        'reference_transaction',
        'reference',
    ];

   protected static function boot()
{
    parent::boot();

    static::created(function ($payement) {

        if ($payement->taxe) {
            $payement->taxe->update([
                'statut' => 'payee'
            ]);
        }

    });
}

    public function taxe()
    {
        return $this->belongsTo(Taxe::class);
    }

    public function agent()
    {
        return $this->belongsTo(Agents::class);
    }

    // accès au contribuable via la taxe
    public function contribuable()
    {
        return $this->belongsTo(Contribuable::class);
    }

     public function commune()
    {
        return $this->belongsTo(Commune::class);
    }

   
}