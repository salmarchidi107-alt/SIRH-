<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tablette extends Model
{
<<<<<<< HEAD
    use \App\Traits\HasTenantScope;

    protected $fillable = [
        'tenant_id',
=======
    protected $fillable = [
>>>>>>> 6b5799881c0e6344d7e3c861606c54fdeaa2dc06
        'nom',
        'tablette_id',
        'token',
        'localisation',
<<<<<<< HEAD
=======
        'derniere_connexion',
>>>>>>> 6b5799881c0e6344d7e3c861606c54fdeaa2dc06
        'active',
    ];

    protected $casts = [
<<<<<<< HEAD
        'active' => 'boolean',
    ];
=======
        'derniere_connexion' => 'datetime',
        'active' => 'boolean',
    ];

    public function pointages()
    {
        return $this->hasMany(Pointage::class);
    }
>>>>>>> 6b5799881c0e6344d7e3c861606c54fdeaa2dc06
}
