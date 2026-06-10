<?php
// app/Models/OrganismeFormation.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Traits\HasTenantScope;


class OrganismeFormation extends Model
{
    use HasFactory, SoftDeletes, HasTenantScope;

    protected $table = 'organismes_formation';

    protected $fillable = [
        'nom', 'adresse', 'telephone',
        'email', 'site_web', 'agree', 'actif', 'date_creation', 'tenant_id',
    ];

    protected $casts = [
        'agree'         => 'boolean',
        'actif'         => 'boolean',
        'date_creation' => 'date',
    ];
}
