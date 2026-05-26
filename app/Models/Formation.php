<?php
// app/Models/Formation.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Cache;

class Formation extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'employee_id',
        'titre',
        'formateur',
        'organisme',
        'date',
        'heure_debut',
        'heure_fin',
        'statut',
        'description',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    /* ═══════════════════════════════════════════════════
     |  SEULE CONSTANTE STATIQUE — les statuts ne changent
     |  pas car ils pilotent la logique métier
     ═══════════════════════════════════════════════════ */

    const STATUTS = ['Planifiée', 'En cours', 'Terminée', 'Annulée'];

    /* ═══════════════════════════════════════════════════
     |  LISTES DYNAMIQUES — 100% depuis la base de données
     |  Cache 60 min, invalidé à chaque CRUD Référentiel
     ═══════════════════════════════════════════════════ */

    /**
     * Titres des formations depuis catalogue_formations
     */
    public static function getTitres(): array
    {
        return Cache::remember('lms_titres', 3600, function () {
            return CatalogueFormation::where('actif', true)
                ->orderBy('titre')
                ->pluck('titre')
                ->toArray();
        });
    }

    /**
     * Noms complets des formateurs depuis la table formateurs
     */
    public static function getFormateurs(): array
    {
        return Cache::remember('lms_formateurs', 3600, function () {
            return Formateur::where('actif', true)
                ->orderBy('nom')
                ->get()
                ->map(fn($f) => trim("{$f->prenom} {$f->nom}"))
                ->filter()
                ->values()
                ->toArray();
        });
    }

    /**
     * Noms des organismes depuis organismes_formation
     */
    public static function getOrganismes(): array
    {
        return Cache::remember('lms_organismes', 3600, function () {
            return OrganismeFormation::where('actif', true)
                ->orderBy('nom')
                ->pluck('nom')
                ->toArray();
        });
    }

    /* ═══════════════════════════════════════════════════
     |  INVALIDATION DU CACHE
     |  Appelé par ReferentielController après chaque CRUD
     ═══════════════════════════════════════════════════ */

    public static function clearCacheTitres(): void
    {
        Cache::forget('lms_titres');
    }

    public static function clearCacheFormateurs(): void
    {
        Cache::forget('lms_formateurs');
    }

    public static function clearCacheOrganismes(): void
    {
        Cache::forget('lms_organismes');
    }

    public static function clearAllCache(): void
    {
        Cache::forget('lms_titres');
        Cache::forget('lms_formateurs');
        Cache::forget('lms_organismes');
    }

    /* ═══════════════════════════════════════════════════
     |  RELATIONS
     ═══════════════════════════════════════════════════ */

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    /* ═══════════════════════════════════════════════════
     |  SCOPES
     ═══════════════════════════════════════════════════ */

    public function scopeParDepartement($query, $departementId)
    {
        return $query->whereHas('employee', fn($q) => $q->where('department_id', $departementId));
    }

    public function scopeParFormation($query, $titre)
    {
        return $query->where('titre', $titre);
    }

    public function scopeParSemaine($query, $debut, $fin)
    {
        return $query->whereBetween('date', [$debut, $fin]);
    }

    public function scopeParStatut($query, $statut)
    {
        return $query->where('statut', $statut);
    }

    /* ═══════════════════════════════════════════════════
     |  ACCESSEURS
     ═══════════════════════════════════════════════════ */

    public function getBadgeClassAttribute(): string
    {
        return match ($this->statut) {
            'Planifiée' => 'badge-planifiee',
            'En cours'  => 'badge-encours',
            'Terminée'  => 'badge-terminee',
            'Annulée'   => 'badge-annulee',
            default     => 'badge-planifiee',
        };
    }

    public function getHoraireAttribute(): string
    {
        return substr($this->heure_debut, 0, 5) . '–' . substr($this->heure_fin, 0, 5);
    }
}
