<?php

namespace App\Models;

use Spatie\Permission\Traits\HasRoles;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Eloquent\Model;
<<<<<<< HEAD

class Agents extends Authenticatable
{
=======
use Laravel\Sanctum\HasApiTokens; // <-- le trait pour API token

class Agents extends Authenticatable
{
     use HasApiTokens; // <-- nécessaire pour createToken()
    
>>>>>>> efa4ca4 (gen committ)
     use HasRoles;
    protected $fillable = [
        'commune_id',
        'nom',
        'telephone',
        'email',
        'role',
        'password'
    ];

<<<<<<< HEAD
=======

     protected $hidden = [
        'password',
        'remember_token',
    ];

    // Mutator pour hasher le password
    public function setPasswordAttribute($value)
    {
        $this->attributes['password'] = bcrypt($value);
    }


>>>>>>> efa4ca4 (gen committ)
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

<<<<<<< HEAD
    public function setPasswordAttribute($value)
    {
        $this->attributes['password'] = Hash::make($value);
    }
=======
    // public function setPasswordAttribute($value)
    // {
    //     $this->attributes['password'] = Hash::make($value);
    // }
>>>>>>> efa4ca4 (gen committ)

    public function commune()
    {
        return $this->belongsTo(Commune::class);
    }
}