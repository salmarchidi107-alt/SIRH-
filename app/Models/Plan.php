<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Plan extends Model
{
    use HasFactory;

    protected $fillable = [
        'nom',
        'slug',
        'max_utilisateurs',
        'duree_essai_jours',
        'prix_mensuel',
        'actif',
    ];

    protected $casts = [
        'actif' => 'boolean',
        'prix_mensuel' => 'decimal:2',
    ];

    // Relation : un plan a plusieurs tenants
    public function tenants()
    {
        return $this->hasMany(Tenant::class);
    }

    // Accesseur : affiche "Illimité" si null
    public function getMaxUtilisateursLabelAttribute(): string
    {
        return $this->max_utilisateurs === null ? 'Illimité' : (string) $this->max_utilisateurs;
    }
}
