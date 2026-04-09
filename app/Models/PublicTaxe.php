<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PublicTaxe extends Model
{
    use HasFactory;

    protected $table = "public_taxes";

    protected $fillable = [
        'user_id',
        'contribuable_id',
        'contribuable_nom',
        'type_taxe_id',
        'montant',
        'periode_debut',
        'periode_fin',
        'reference',
        'status',
        'commentaire_admin',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function contribuable()
    {
        return $this->belongsTo(Contribuable::class);
    }

    public function typeTaxe()
    {
        return $this->belongsTo(TypeTaxe::class, 'type_taxe_id');
    }
}