<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tablette extends Model
{
    use \App\Traits\HasTenantScope;

    protected $fillable = [
        'tenant_id',
        'nom',
        'tablette_id',
        'token',
        'localisation',
        'active',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];
}
