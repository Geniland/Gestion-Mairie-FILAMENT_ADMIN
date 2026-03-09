<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Amendes extends Model
{
    protected $fillable = [
        'commune_id',
        'contribuable_id',
        'motif',
        'montant',
        'statut'
    ];

    public function commune()
    {
        return $this->belongsTo(Commune::class);
    }

    public function contribuable()
    {
        return $this->belongsTo(Contribuable::class);
    }
}