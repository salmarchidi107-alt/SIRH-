<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

class Pointage extends Model
{
    use \App\Traits\HasTenantScope;
    protected $fillable = [
        'tenant_id',
        'employee_id',
        'date',
        'heure_entree',
        'heure_sortie',
        'pause_minutes',
        'total_heures',
        'statut',
        'valide',
        'ignore_badge',
        'source',
        'tablette_id',
        'geolng',
        'derniere_sync',
        'heures_travaillees',
        'heures_supplementaires',
    ];

    protected $casts = [
        'date'          => 'date',
        'valide'        => 'boolean',
        'ignore_badge'  => 'boolean',
        'total_heures'  => 'decimal:2',
        'derniere_sync' => 'datetime',
        'heures_travaillees' => 'decimal:2',
        'heures_supplementaires' => 'decimal:2',
    ];

    // ── Relations ──────────────────────────────────────────────
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function events(): HasMany
    {
        return $this->hasMany(PointageEvent::class);
    }

    // ── Accesseurs ─────────────────────────────────────────────
    public function getTotalHeuresFormateAttribute(): string
    {
        if (!$this->total_heures) return '—';
        return number_format($this->total_heures, 2) . 'h';
    }

    public function getStatutLabelAttribute(): string
    {
        return match ($this->statut) {
            'present'             => 'Présent',
            'absent'              => 'Absent',
            'absence_injustifiee' => 'Absence injustifiée',
            'pas_de_badge'        => 'Pas de badge',
            default               => '—',
        };
    }

    // ── Scopes ─────────────────────────────────────────────────
    public function scopeForDate($query, string $date)
    {
        return $query->where('date', $date);
    }

    public function scopeForWeek($query, Carbon $start, Carbon $end)
    {
        return $query->whereBetween('date', [$start->toDateString(), $end->toDateString()]);
    }

    public function scopeValides($query)
    {
        return $query->where('valide', true);
    }

    public function scopeParEmployee($query, $employeeId)
    {
        return $query->where('employee_id', $employeeId);
    }

    public function scopeParMois($query, $annee, $mois)
    {
        return $query->whereYear('date', $annee)->whereMonth('date', $mois);
    }

    public function scopeParAnnee($query, $annee)
    {
        return $query->whereYear('date', $annee);
    }

    public function scopeParSemaine($query, $debutSem, $finSem)
    {
        return $query->whereBetween('date', [$debutSem, $finSem]);
    }

    // ── Méthodes ───────────────────────────────────────────────
    public function calculerTotalHeures(): void
    {
        if ($this->heure_entree && $this->heure_sortie) {
            $entree = Carbon::parse($this->date->toDateString() . ' ' . $this->heure_entree);
            $sortie = Carbon::parse($this->date->toDateString() . ' ' . $this->heure_sortie);

            // Gestion passage minuit
            if ($sortie->lessThan($entree)) {
                $sortie->addDay();
            }

            $minutes = $entree->diffInMinutes($sortie) - $this->pause_minutes;
            $this->total_heures = round($minutes / 60, 2);
            $this->save();
        }
    }
}
