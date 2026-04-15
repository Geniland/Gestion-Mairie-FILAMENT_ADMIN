<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PublicPayment extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'public_taxe_id',
        'montant',
        'reference',
        'transaction_id',
        'checkout_url',
        'status',
    ];

    public function taxe()
    {
        return $this->belongsTo(PublicTaxe::class, 'public_taxe_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}