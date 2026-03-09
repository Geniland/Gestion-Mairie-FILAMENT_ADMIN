<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TypeTaxe extends Model
{
     protected $table = 'types_taxes';

    protected $fillable = [
        'commune_id',
        'nom',
        'description',
        'montant_base',
        'periode',
        'actif'
    ];

    /**
     * Relation avec les taxes
     * Un type de taxe peut avoir plusieurs taxes
     */
    public function taxe()
    {
        return $this->hasMany(Taxe::class);
    }

      public function commune()
    {
        return $this->belongsTo(Commune::class);
    }
}
