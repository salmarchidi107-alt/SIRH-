<?php

namespace App\Models;

use App\Traits\HasTenantScope;
use Illuminate\Database\Eloquent\Model;

class DocumentEntete extends Model
{
    use HasTenantScope;

    protected $fillable = [
        'nom', 'type', 'logo_path', 'nom_societe', 'adresse',
        'telephone', 'email', 'site_web', 'rc', 'ice',
        'contenu_libre', 'contenu_pied_de_page', 'actif', 'created_by',
        'tenant_id',
    ];

    protected $casts = [
        'actif' => 'boolean',
    ];

    public static function getActive(): ?self
    {
        return self::where('actif', true)->latest()->first();
    }
}