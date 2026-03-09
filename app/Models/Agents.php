<?php

namespace App\Models;

use Spatie\Permission\Traits\HasRoles;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Eloquent\Model;

class Agents extends Authenticatable
{
     use HasRoles;
    protected $fillable = [
        'commune_id',
        'nom',
        'telephone',
        'email',
        'role',
        'password'
    ];

     // Méthode pour Filament
    // public function getFilamentName(): string
    // {
    //     return $this->nom; // ou concaténer nom + prénom si tu as
    // }

    public function getNameAttribute(): string
    {
        return $this->nom;
    }

      // Pour savoir si c'est un super admin
    public function isSuperAdmin(): bool
    {
        return $this->role === 'super_admin';
    }

    // Pour savoir si c'est un maire
    public function isMaire(): bool
    {
        return $this->role === 'maire';
    }

    // Pour savoir si c'est un agent
    public function isAgent(): bool
    {
        return $this->role === 'agent';
    }

    public function setPasswordAttribute($value)
    {
        $this->attributes['password'] = Hash::make($value);
    }

    public function commune()
    {
        return $this->belongsTo(Commune::class);
    }
}