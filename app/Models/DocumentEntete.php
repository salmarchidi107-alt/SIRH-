<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DocumentEntete extends Model
{
    protected $fillable = [
        'nom', 'logo_path', 'nom_societe', 'adresse',
        'telephone', 'email', 'site_web', 'rc', 'ice',
        'contenu_libre', 'actif', 'created_by',
    ];

    protected $casts = [
        'actif' => 'boolean',
    ];

    // Récupérer l'entête active (singleton global)
    public static function getActive(): ?self
    {
        return self::where('actif', true)->latest()->first();
    }
}