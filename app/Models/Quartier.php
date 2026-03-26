<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Quartier extends Model
{
     protected $fillable = [
        'commune_id',
        'nom'
    ];

    public function commune()
    {
        return $this->belongsTo(Commune::class);
    }
}
