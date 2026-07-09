<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\DB;

class Equipement extends Model
{
    use HasFactory, SoftDeletes;

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

    /**
     * Génère une référence unique par tenant + catégorie.
     *
     * IMPORTANT :
     * - Se base sur le MAX du suffixe numérique existant (pas un count()),
     *   pour ne jamais réutiliser un numéro déjà pris après une suppression.
     * - Utilise withTrashed() car la colonne "reference" reste occupée
     *   en base même pour les équipements soft-deleted (contrainte unique
     *   au niveau SQL, indépendante du soft delete).
     * - lockForUpdate() à l'intérieur d'une transaction pour éviter que deux
     *   requêtes concurrentes (double clic, retry réseau) génèrent la même
     *   référence avant que l'une des deux ait committé son insert.
     *
     * Cette méthode DOIT être appelée à l'intérieur d'une DB::transaction()
     * qui englobe aussi le Equipement::create() (voir EquipementController::store).
     */
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

        // Longueur du préfixe + 1 pour le tiret => position du numéro (1-indexed pour SUBSTRING)
        $offset = strlen($prefix) + 2;

        $last = static::withTrashed()
            ->where('tenant_id', $tenantId)
            ->where('reference', 'like', $prefix . '-%')
            ->lockForUpdate()
            ->orderByRaw('CAST(SUBSTRING(reference, ' . $offset . ') AS UNSIGNED) DESC')
            ->first();

        $nextNumber = $last
            ? ((int) substr($last->reference, $offset - 1)) + 1
            : 1;

        return $prefix . '-' . str_pad($nextNumber, 5, '0', STR_PAD_LEFT);
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
