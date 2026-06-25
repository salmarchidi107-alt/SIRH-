<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Traits\HasTenantScope;

class Equipement extends Model
{
    use HasFactory, SoftDeletes, HasTenantScope;

    protected $table = 'equipements';

    protected $fillable = [
        'tenant_id',
        'reference',
        'designation',
        'categorie',
        'marque',
        'modele',
        'numero_serie',
        'date_acquisition',
        'fournisseur',
        'valeur_acquisition',
        'etat',
        'statut',
        'localisation',
        'observations',
    ];

    protected $casts = [
        'date_acquisition'   => 'date',
        'valeur_acquisition' => 'decimal:2',
    ];

    // ─── Relations ────────────────────────────────────────────────────────────

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function affectations()
    {
        return $this->hasMany(AffectationEquipement::class, 'equipement_id');
    }

    public function affectationActive()
    {
        return $this->hasOne(AffectationEquipement::class, 'equipement_id')
                    ->where('statut', 'Actif')
                    ->latest();
    }

    // ─── Scopes ───────────────────────────────────────────────────────────────

    public function scopeDisponibles($query)
    {
        return $query->where('statut', 'Disponible');
    }

    public function scopeAffectes($query)
    {
        return $query->where('statut', 'Affecté');
    }

    public function scopeForTenant($query, string $tenantId)
    {
        return $query->where('tenant_id', $tenantId);
    }

    // ─── Helpers ──────────────────────────────────────────────────────────────

    public static function genererReference(string $categorie, string $tenantId): string
    {
        $prefixes = [
            'Ordinateur portable' => 'PC',
            'Téléphone'           => 'TEL',
            'Tablette'            => 'TAB',
            'Véhicule'            => 'VEH',
            'Badge'               => 'BAD',
            'EPI'                 => 'EPI',
            'Uniforme'            => 'UNI',
            'Mobilier'            => 'MOB',
            'Autre'               => 'EQ',
        ];

        $prefix = $prefixes[$categorie] ?? 'EQ';
        $count  = static::where('tenant_id', $tenantId)
                        ->where('categorie', $categorie)
                        ->count() + 1;

        return $prefix . '-' . str_pad($count, 5, '0', STR_PAD_LEFT);
    }

    // ─── Accesseurs couleur (utilisés dans les vues Blade) ───────────────────

    public function getCategorieColorAttribute(): string
    {
        return match ($this->categorie) {
            'Ordinateur portable' => 't-blue',
            'Téléphone'           => 't-teal',
            'Tablette'            => 't-blue',
            'Véhicule'            => 't-teal',
            'Badge'               => 't-gray',
            'EPI'                 => 't-amber',
            'Uniforme'            => 't-gray',
            'Mobilier'            => 't-gray',
            default               => 't-gray',
        };
    }

    public function getEtatColorAttribute(): string
    {
        return match ($this->etat) {
            'Neuf'         => 't-green',
            'Bon état'     => 't-amber',
            'À réparer'    => 't-red',
            'Hors service' => 't-red',
            default        => 't-gray',
        };
    }

    public function getStatutColorAttribute(): string
    {
        return match ($this->statut) {
            'Disponible'  => 't-green',
            'Affecté'     => 't-blue',
            'Maintenance' => 't-amber',
            'Perdu'       => 't-red',
            default       => 't-gray',
        };
    }
}
