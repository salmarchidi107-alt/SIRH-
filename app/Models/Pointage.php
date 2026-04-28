<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;
use \App\Traits\HasTenantScope;

class Pointage extends Model
{
    protected $fillable = [
        'tenant_id',
        'employee_id',
        'date',
        'heure_entree',
        'heure_sortie',
        'pause_start',
        'pause_end',
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
        'pause_start'   => 'datetime:H:i:s',
        'pause_end'     => 'datetime:H:i:s',
        'valide'        => 'boolean',
        'ignore_badge'  => 'boolean',
        'total_heures'  => 'decimal:2',
        'derniere_sync' => 'datetime',
        'heures_travaillees'     => 'decimal:2',
        'heures_supplementaires' => 'decimal:2',
    ];

    // ─────────────────────────────────────────────────────────────────────────
    // Boot
    // ─────────────────────────────────────────────────────────────────────────

    public static function boot(): void
    {
        parent::boot();

        static::saving(function ($pointage) {
            if ($pointage->heure_entree && $pointage->heure_sortie && $pointage->statut !== 'absent') {
                $should_save = false;
                $pointage->calculerTotalHeures($should_save);
            }
        });
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Relations
    // ─────────────────────────────────────────────────────────────────────────

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function events(): HasMany
    {
        return $this->hasMany(PointageEvent::class);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Accesseurs
    // ─────────────────────────────────────────────────────────────────────────

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

    public function getPauseFormateeAttribute(): string
    {
        if (!$this->pause_minutes || $this->pause_minutes === 0) {
            return '—';
        }
        $hours = floor($this->pause_minutes / 60);
        $mins  = $this->pause_minutes % 60;
        return $hours ? "{$hours}h {$mins}m" : "{$mins}m";
    }

    public function getPauseDebutAttribute(): ?string
    {
        return $this->pause_start?->format('H:i') ?? null;
    }

    public function getPauseFinAttribute(): ?string
    {
        return $this->pause_end?->format('H:i') ?? null;
    }

    public function getDebutShiftAttribute(): ?string
    {
        return $this->heure_entree?->format('H:i') ?? null;
    }

    public function getFinShiftAttribute(): ?string
    {
        return $this->heure_sortie?->format('H:i') ?? null;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Scopes
    // ─────────────────────────────────────────────────────────────────────────

    public function scopeForDate($query, string $date)
    {
        return $query->whereDate('date', $date);
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

    // ─────────────────────────────────────────────────────────────────────────
    // Calcul principal
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Calcule et affecte heures_travaillees, heures_supplementaires, total_heures.
     *
     * Ordre de priorité pour la pause :
     *   1. Si des PointageEvent (badge records) sont chargés via ->events,
     *      on recalcule la pause depuis ces events (même logique que PointageController).
     *   2. Sinon on utilise pause_start / pause_end déjà stockés sur le modèle.
     *   3. En dernier recours on utilise pause_minutes déjà stocké.
     *
     * @param bool $save  Persiste ou non après le calcul.
     */
    public function calculerTotalHeures(bool $save = true): void
    {
        if (!$this->heure_entree || !$this->heure_sortie) {
            return;
        }

        $dateStr = $this->date instanceof Carbon
            ? $this->date->toDateString()
            : Carbon::parse($this->date)->toDateString();

        $entree = Carbon::parse("{$dateStr} {$this->heure_entree}");
        $sortie = Carbon::parse("{$dateStr} {$this->heure_sortie}");

        // Passage minuit
        if ($sortie->lessThan($entree)) {
            $sortie->addDay();
        }

        // ── Calcul des minutes de pause ──────────────────────────────────────
        $pauseMinutes = $this->calculerPauseMinutes();

        // Mise à jour de pause_minutes si on a pu le recalculer
        $this->pause_minutes = $pauseMinutes;

        // ── Calcul du temps travaillé ────────────────────────────────────────
        $minutesBrutes  = $entree->diffInMinutes($sortie);
        $minutesNettes  = max(0, $minutesBrutes - $pauseMinutes);
        $totalHeures    = round($minutesNettes / 60, 2);

        // Split normal vs supplémentaires (standard 8h/jour)
        $this->heures_travaillees       = min($totalHeures, 8.0);
        $this->heures_supplementaires   = max(0.0, $totalHeures - 8.0);
        $this->total_heures             = $totalHeures;

        if ($save) {
            $this->save();
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Calcul de pause — logique unifiée
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Retourne les minutes de pause en appliquant la même logique que
     * PointageController::calcPauseMinutes(), avec repli sur pause_start/pause_end
     * puis sur pause_minutes déjà stocké.
     */
    public function calculerPauseMinutes(): int
    {
        // ── Priorité 1 : events badge (PointageEvent chargés en relation) ────
        if ($this->relationLoaded('events') && $this->events->isNotEmpty()) {
            return $this->calcPauseDepuisEvents($this->events);
        }

        // ── Priorité 2 : pause_start / pause_end stockés sur le modèle ───────
        if ($this->pause_start && $this->pause_end) {
            return $this->calcPauseDepuisStartEnd(
                $this->pause_start,
                $this->pause_end
            );
        }

        // ── Priorité 3 : pause_minutes déjà persisté ─────────────────────────
        return (int) ($this->pause_minutes ?? 0);
    }

    /**
     * Calcule les minutes de pause depuis une collection de PointageEvent.
     * Logique identique à PointageController::calcPauseMinutes().
     */
    private function calcPauseDepuisEvents(\Illuminate\Support\Collection $events): int
    {
        $pausesStart = $events
            ->where('type', 'pause_start')
            ->sortBy('created_at')
            ->pluck('created_at')
            ->values();

        $pausesEnd = $events
            ->where('type', 'pause_end')
            ->sortBy('created_at')
            ->pluck('created_at')
            ->values();

        if ($pausesStart->isEmpty() || $pausesEnd->isEmpty()) {
            return 0;
        }

        $total = 0;
        $count = min($pausesStart->count(), $pausesEnd->count());

        for ($i = 0; $i < $count; $i++) {
            $start = strtotime($pausesStart[$i]);
            $end   = strtotime($pausesEnd[$i]);

            if ($end > $start) {
                $total += ($end - $start);
            }
        }

        return (int) floor($total / 60);
    }

    /**
     * Calcule les minutes de pause depuis pause_start et pause_end
     * déjà stockés sur le modèle (un seul créneau de pause).
     */
    private function calcPauseDepuisStartEnd(mixed $start, mixed $end): int
    {
        $dateStr = $this->date instanceof Carbon
            ? $this->date->toDateString()
            : Carbon::parse($this->date)->toDateString();

        // Les champs sont castés datetime:H:i:s, on les normalise en Carbon
        $debut = Carbon::parse("{$dateStr} " . (
            $start instanceof Carbon ? $start->format('H:i:s') : $start
        ));
        $fin   = Carbon::parse("{$dateStr} " . (
            $end instanceof Carbon ? $end->format('H:i:s') : $end
        ));

        // Pause sur passage minuit (rare mais possible)
        if ($fin->lessThan($debut)) {
            $fin->addDay();
        }

        $minutes = $debut->diffInMinutes($fin);

        // Sanity check : une pause > 4h est probablement une erreur de saisie
        return $minutes > 240 ? 0 : (int) $minutes;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Recalcul avec rechargement des events depuis la BDD
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Recharge les events depuis la BDD puis recalcule les heures.
     * Utile quand on veut forcer le recalcul complet sans avoir chargé la relation.
     */
    public function recalculerAvecEvents(bool $save = true): void
    {
        $this->load('events');
        $this->calculerTotalHeures($save);
    }
}