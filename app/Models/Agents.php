<?php

namespace App\Models;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Eloquent\Model;

class Agents extends Model
{
    protected $fillable = [
        'commune_id',
        'nom',
        'telephone',
        'email',
        'role',
        'password'
    ];

    public function setPasswordAttribute($value)
{
    $this->attributes['password'] = Hash::make($value);
}

    public function commune()
    {
        return $this->belongsTo(Commune::class);
    }
}