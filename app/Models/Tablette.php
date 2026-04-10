<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tablette extends Model
{
    protected $fillable = [
        'nom',
        'tablette_id',
        'token',
        'localisation',
        'derniere_connexion',
        'active',
    ];

    protected $casts = [
        'derniere_connexion' => 'datetime',
        'active' => 'boolean',
    ];

    public function pointages()
    {
        return $this->hasMany(Pointage::class);
    }
}
