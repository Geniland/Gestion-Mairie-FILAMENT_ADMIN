<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Taxe extends Model
{
    protected $fillable = [
        'commune_id',
        'contribuable_id',
        'type_taxe_id',
        'agent_id',
        'montant',
        'periode_debut',
        'periode_fin',
        'statut'
    ];

    public function getNomTaxeAttribute()
    {
        return $this->typeTaxe->nom . ' - ' . $this->montant;
    }

    public function commune()
    {
        return $this->belongsTo(Commune::class);
    }

    public function contribuable()
    {
        return $this->belongsTo(Contribuable::class);
    }

    public function details()
    {
        return $this->hasOne(TaxeDetails::class);
    }

    public function payement()
    {
        return $this->hasMany(Payement::class);
    }

    public function tickets()
    {
        return $this->hasOne(Tickets::class);
    }

    public function typeTaxe()
    {
        return $this->belongsTo(TypeTaxe::class);
    }

    public function agent()
    {
        return $this->belongsTo(Agents::class);
    }

    
}