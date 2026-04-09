<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Actualite extends Model
{
    use HasFactory;

    protected $fillable = ['titre', 'resume', 'contenu', 'image', 'published_at'];

    protected $casts = [
        'published_at' => 'datetime',
    ];
}
