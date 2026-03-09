<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TaxeDetails extends Model
{
    protected $fillable = [
        'commune_id',
        'taxe_id',
        'details'
    ];

    protected $casts = [
        'details' => 'array'
    ];

    public function taxe()
    {
        return $this->belongsTo(Taxe::class);
    }

     public function commune()
    {
        return $this->belongsTo(Commune::class);
    }
}