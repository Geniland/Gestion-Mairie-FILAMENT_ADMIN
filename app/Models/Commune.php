<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Commune extends Model
{
    protected $fillable = [
        'nom',
        'region',
        'quartier'
    ];

    public function contribuable()
    {
        return $this->hasMany(Contribuable::class);
    }

    public function taxe()
    {
        return $this->hasMany(Taxe::class);
    }

    public function amendes()
    {
        return $this->hasMany(Amendes::class);
    }

    public function agents()
    {
        return $this->hasMany(Agents::class);
    }
}