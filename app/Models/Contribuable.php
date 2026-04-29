<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Contribuable extends Model
{
    protected $fillable = [
        'commune_id',
        'agent_id',
        'nom',
        'telephone',
        'type',
        'numero_identifiant',
        'adresse',
        'is_blocked',
        'blocked_reason'
    ];

    public function agent()
    {
        return $this->belongsTo(Agents::class);
    }

    public function commune()
    {
        return $this->belongsTo(Commune::class);
    }

    public function taxe()
    {
        return $this->hasMany(Taxe::class);
    }

    public function payement()
    {
        return $this->hasMany(Payement::class);
    }

    public function amendes()
    {
        return $this->hasMany(Amendes::class);
    }
}