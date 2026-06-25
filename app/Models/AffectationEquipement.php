<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Traits\HasTenantScope;


class AffectationEquipement extends Model
{
    use HasFactory, HasTenantScope;

    protected $table = 'affectations_equipement';

    protected $fillable = [
        'tenant_id',
        'equipement_id',
        'employee_id',
        'date_affectation',
        'date_retour_prevue',
        'date_retour_effectif',
        'etat_remise',
        'etat_retour',
        'observations',
        'observations_retour',
        'statut',
        'numero_decharge',
        'decharge_signee',
    ];

    protected $casts = [
        'date_affectation'      => 'date',
        'date_retour_prevue'    => 'date',
        'date_retour_effectif'  => 'date',
        'decharge_signee'       => 'boolean',
    ];

    // ─── Relations ────────────────────────────────────────────────────────────

    public function equipement()
    {
        return $this->belongsTo(Equipement::class, 'equipement_id');
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    // ─── Scopes ───────────────────────────────────────────────────────────────

    public function scopeActives($query)
    {
        return $query->where('statut', 'Actif');
    }

    public function scopeForTenant($query, string $tenantId)
    {
        return $query->where('tenant_id', $tenantId);
    }

    // ─── Helpers ──────────────────────────────────────────────────────────────

    public static function genererNumeroDecharge(string $tenantId): string
    {
        $annee = date('Y');
        $count = static::where('tenant_id', $tenantId)
                       ->whereYear('created_at', $annee)
                       ->count() + 1;

        return 'DCH-' . $annee . '-' . str_pad($count, 5, '0', STR_PAD_LEFT);
    }
}
