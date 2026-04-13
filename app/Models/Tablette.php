<?php

namespace App\Models;

use \App\Traits\HasTenantScope;
use Illuminate\Database\Eloquent\Model;

class Tablette extends Model
{

    protected $fillable = [
        'tenant_id',
        'nom',
        'tablette_id',
        'token',
        'localisation',
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
