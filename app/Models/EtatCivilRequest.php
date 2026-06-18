<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EtatCivilRequest extends Model
{
    use HasFactory;

    protected $table = 'etat_civil_requests';

    protected $fillable = [
        'user_id',
        'commune_id',
        'reference',
        'nom',
        'telephone',
        'email',
        'type',
        'details',
        'files',
        'status',
        'document_url',
        'commentaire_admin',
    ];

    public function commune()
    {
        return $this->belongsTo(Commune::class);
    }

    protected $casts = [
        'files' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
