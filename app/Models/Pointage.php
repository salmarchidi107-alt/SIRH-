<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
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
        'valide'        => 'boolean',
        'ignore_badge'  => 'boolean',
        'total_heures'  => 'decimal:2',
        'derniere_sync' => 'datetime',
        'heures_travaillees'     => 'decimal:2',
        'heures_supplementaires' => 'decimal:2',
    ];

    public static function boot(): void
    {
        parent::boot();
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
        return number_format((float) $this->total_heures, 2) . 'h';
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
        $minutes = (int) ($this->getRawOriginal('pause_minutes') ?? $this->pause_minutes ?? 0);
        if ($minutes === 0) return '—';
        $hours = floor($minutes / 60);
        $mins  = $minutes % 60;
        return $hours ? "{$hours}h {$mins}m" : "{$mins}m";
    }

    public function getPauseDebutAttribute(): ?string
    {
        $raw = $this->getRawOriginal('pause_start') ?? $this->pause_start;
        if (!$raw) return null;
        try { return Carbon::parse($raw)->format('H:i'); } catch (\Exception $e) { return null; }
    }

    public function getPauseFinAttribute(): ?string
    {
        $raw = $this->getRawOriginal('pause_end') ?? $this->pause_end;
        if (!$raw) return null;
        try { return Carbon::parse($raw)->format('H:i'); } catch (\Exception $e) { return null; }
    }

    public function getDebutShiftAttribute(): ?string
    {
        $raw = $this->getRawOriginal('heure_entree') ?? $this->heure_entree;
        if (!$raw) return null;
        try { return Carbon::parse($raw)->format('H:i'); } catch (\Exception $e) { return null; }
    }

    public function getFinShiftAttribute(): ?string
    {
        $raw = $this->getRawOriginal('heure_sortie') ?? $this->heure_sortie;
        if (!$raw) return null;
        try { return Carbon::parse($raw)->format('H:i'); } catch (\Exception $e) { return null; }
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

    public function calculerTotalHeures(bool $save = true): void
    {
        $heureEntreeRaw = $this->getRawOriginal('heure_entree') ?? $this->heure_entree;
        $heureSortieRaw = $this->getRawOriginal('heure_sortie') ?? $this->heure_sortie;

        if (!$heureEntreeRaw || !$heureSortieRaw) {
            return;
        }

        $dateStr = $this->date instanceof Carbon
            ? $this->date->toDateString()
            : Carbon::parse($this->date)->toDateString();

        try {
            $entree = Carbon::parse("{$dateStr} {$heureEntreeRaw}");
            $sortie = Carbon::parse("{$dateStr} {$heureSortieRaw}");
        } catch (\Exception $e) {
            return;
        }

        if ($sortie->lessThanOrEqualTo($entree)) {
            $sortie->addDay();
        }

        $pauseMinutes = $this->calculerPauseMinutes();

        $minutesBrutes = $entree->diffInMinutes($sortie);
        $minutesNettes = max(0, $minutesBrutes - $pauseMinutes);
        $totalHeures   = round($minutesNettes / 60, 2);

        $heuresTravaillees     = min($totalHeures, 8.0);
        $heuresSupplementaires = max(0.0, $totalHeures - 8.0);

        $this->pause_minutes           = $pauseMinutes;
        $this->heures_travaillees      = $heuresTravaillees;
        $this->heures_supplementaires  = $heuresSupplementaires;
        $this->total_heures            = $totalHeures;

        if ($save && $this->id) {
            DB::table('pointages')
                ->where('id', $this->id)
                ->update([
                    'pause_minutes'          => $pauseMinutes,
                    'heures_travaillees'     => $heuresTravaillees,
                    'heures_supplementaires' => $heuresSupplementaires,
                    'total_heures'           => $totalHeures,
                    'updated_at'             => now(),
                ]);

            $this->syncOriginal();
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Calcul pause — utilise badge_records via le controller
    // La priorité 2 (pause_start/pause_end) est utilisée quand les champs
    // sont déjà persistés en base après syncPointageFromBadgeRecords
    // ─────────────────────────────────────────────────────────────────────────

    public function calculerPauseMinutes(): int
    {
        // Priorité 1 : events PointageEvent chargés en relation
        if ($this->relationLoaded('events') && $this->events->isNotEmpty()) {
            return $this->calcPauseDepuisEvents($this->events);
        }

        // Priorité 2 : pause_start / pause_end persistés en base
        $pauseStart = $this->getRawOriginal('pause_start') ?? $this->pause_start;
        $pauseEnd   = $this->getRawOriginal('pause_end')   ?? $this->pause_end;

        if ($pauseStart && $pauseEnd) {
            return $this->calcPauseDepuisStartEnd($pauseStart, $pauseEnd);
        }

        // Priorité 3 : pause_minutes déjà persisté (calculé par le controller)
        $raw = $this->getRawOriginal('pause_minutes') ?? $this->pause_minutes ?? 0;
        return (int) $raw;
    }

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

    private function calcPauseDepuisStartEnd(mixed $start, mixed $end): int
    {
        $dateStr = $this->date instanceof Carbon
            ? $this->date->toDateString()
            : Carbon::parse($this->date)->toDateString();

        try {
            $debut = Carbon::parse("{$dateStr} {$start}");
            $fin   = Carbon::parse("{$dateStr} {$end}");
        } catch (\Exception $e) {
            return 0;
        }

        if ($fin->lessThan($debut)) {
            $fin->addDay();
        }

        $minutes = $debut->diffInMinutes($fin);
        return $minutes > 240 ? 0 : (int) $minutes;
    }

    public function recalculerAvecEvents(bool $save = true): void
    {
        $this->load('events');
        $this->calculerTotalHeures($save);
    }
}
