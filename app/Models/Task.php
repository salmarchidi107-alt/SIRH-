<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class Task extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'project_id',
        'user_id',
        'title',
        'description',
        'priority',
        'status',
        'due_date',
        'estimated_minutes',
        'timer_started_at',
        'completed_at',
    ];

    protected $casts = [
        'due_date' => 'date',
        'timer_started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public const PRIORITIES = ['faible', 'normale', 'haute', 'urgente'];

    public const STATUSES = ['a_faire', 'en_cours', 'en_pause', 'terminee', 'annulee'];

    // Alignés sur les classes .badge-* déjà utilisées dans tes autres vues (cf. reporting/index.blade.php)
    public const STATUS_LABELS = [
        'a_faire' => 'À faire',
        'en_cours' => 'En cours',
        'en_pause' => 'En pause',
        'terminee' => 'Terminée',
        'annulee' => 'Annulée',
    ];

    public const STATUS_BADGES = [
        'a_faire' => 'badge-gray',
        'en_cours' => 'badge-blue',
        'en_pause' => 'badge-warn',
        'terminee' => 'badge-ok',
        'annulee' => 'badge-bad',
    ];

    public const PRIORITY_LABELS = [
        'faible' => 'Faible',
        'normale' => 'Normale',
        'haute' => 'Haute',
        'urgente' => 'Urgente',
    ];

    public const PRIORITY_BADGES = [
        'faible' => 'badge-gray',
        'normale' => 'badge-blue',
        'haute' => 'badge-warn',
        'urgente' => 'badge-bad',
    ];

    public function statusLabel(): string
    {
        return self::STATUS_LABELS[$this->status] ?? $this->status;
    }

    public function statusBadgeClass(): string
    {
        return self::STATUS_BADGES[$this->status] ?? 'badge-gray';
    }

    public function priorityLabel(): string
    {
        return self::PRIORITY_LABELS[$this->priority] ?? $this->priority;
    }

    public function priorityBadgeClass(): string
    {
        return self::PRIORITY_BADGES[$this->priority] ?? 'badge-gray';
    }

    protected static function booted(): void
    {
        // Une tâche hérite toujours du tenant (et, par sécurité, du user_id
        // si absent) de son projet parent — évite toute incohérence si le
        // projet et la tâche n'appartenaient pas au même employé/tenant.
        static::creating(function (Task $task) {
            if ($task->project_id && $project = Project::find($task->project_id)) {
                $task->tenant_id = $task->tenant_id ?: $project->tenant_id;
                $task->user_id = $task->user_id ?: $project->user_id;
            } elseif (! $task->tenant_id) {
                $task->tenant_id = Auth::user()?->tenant_id;
            }
        });
    }

    /** Filtre sur le tenant courant (ou celui passé en paramètre). */
    public function scopeTenant($query, ?string $tenantId = null)
    {
        $tenantId = $tenantId ?? config('app.current_tenant_id') ?? Auth::user()?->tenant_id;

        return $tenantId ? $query->where('tenant_id', $tenantId) : $query;
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function activities(): HasMany
    {
        return $this->hasMany(Activity::class)->orderByDesc('activity_date')->orderByDesc('id');
    }

    /* ------------------------------------------------------------------ */
    /*  Temps                                                              */
    /* ------------------------------------------------------------------ */

    /** Minutes totales déjà enregistrées (sessions + saisies manuelles). */
    public function getLoggedMinutesAttribute(): int
    {
        return (int) $this->activities()->sum('duration_minutes');
    }

    /** Vrai si un chronomètre est actuellement lancé sur cette tâche. */
    public function getIsTimerRunningAttribute(): bool
    {
        return ! is_null($this->timer_started_at);
    }

    /** Minutes écoulées depuis le lancement du chronomètre en cours (0 si aucun). */
    public function getRunningMinutesAttribute(): int
    {
        if (! $this->timer_started_at) {
            return 0;
        }

        return (int) $this->timer_started_at->diffInMinutes(Carbon::now());
    }

    public function isLate(): bool
    {
        return $this->due_date
            && $this->due_date->isPast()
            && ! in_array($this->status, ['terminee', 'annulee']);
    }

    /** Pourcentage temps loggé / temps estimé, plafonné à 100 (null si pas d'estimation). */
    public function getTimeProgressPercentAttribute(): ?int
    {
        if (! $this->estimated_minutes) {
            return null;
        }

        return (int) min(100, round(($this->logged_minutes / $this->estimated_minutes) * 100));
    }

    /* ------------------------------------------------------------------ */
    /*  Actions liées au chronomètre                                       */
    /* ------------------------------------------------------------------ */

    public function startTimer(): void
    {
        $this->update([
            'timer_started_at' => Carbon::now(),
            'status' => 'en_cours',
        ]);
    }

    /**
     * Arrête le chronomètre en cours et journalise le temps écoulé
     * sous forme d'une Activity de type "chrono".
     */
    public function stopTimer(string $newStatus): ?Activity
    {
        if (! $this->timer_started_at) {
            $this->update(['status' => $newStatus]);
            return null;
        }

        $minutes = max(1, $this->running_minutes);

        $activity = $this->activities()->create([
            'tenant_id' => $this->tenant_id,
            'user_id' => $this->user_id,
            'type' => 'chrono',
            'activity_date' => Carbon::now()->toDateString(),
            'duration_minutes' => $minutes,
            'comment' => null,
        ]);

        $this->update([
            'timer_started_at' => null,
            'status' => $newStatus,
            'completed_at' => $newStatus === 'terminee' ? Carbon::now() : $this->completed_at,
        ]);

        return $activity;
    }
}
