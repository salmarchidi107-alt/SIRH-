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
        'assigned_to',
        'title',
        'description',
        'priority',
        'status',
        'start_date',
        'due_date',
        'estimated_minutes',
        'percent_complete',
        'employee_comment',
        'timer_started_at',
        'completed_at',
    ];

    protected $casts = [
        'start_date' => 'date',
        'due_date' => 'date',
        'percent_complete' => 'integer',
        'timer_started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public const PRIORITIES = ['faible', 'normale', 'haute', 'urgente'];

    public const STATUSES = ['a_faire', 'en_cours', 'en_pause', 'terminee', 'annulee'];

    // Champs que l'employé assigné a le droit de modifier lui-même
    // (cf. espace employé > Mes tâches). Tout le reste (projet, titre,
    // description, priorité, dates, estimation) reste piloté par l'admin.
    public const EMPLOYEE_EDITABLE_FIELDS = ['status', 'percent_complete', 'employee_comment'];

    public const STATUS_LABELS = [
        'a_faire' => 'À faire',
        'en_cours' => 'En cours',
        'en_pause' => 'En pause',
        'terminee' => 'Terminée',
        'annulee' => 'Annulée',
    ];

    public const STATUS_BADGES = [
        'a_faire' => 'sa-badge-gray',
        'en_cours' => 'sa-badge-blue',
        'en_pause' => 'sa-badge-amber',
        'terminee' => 'sa-badge-green',
        'annulee' => 'sa-badge-red',
    ];

    public const PRIORITY_LABELS = [
        'faible' => 'Faible',
        'normale' => 'Normale',
        'haute' => 'Haute',
        'urgente' => 'Urgente',
    ];

    public const PRIORITY_BADGES = [
        'faible' => 'sa-badge-gray',
        'normale' => 'sa-badge-blue',
        'haute' => 'sa-badge-amber',
        'urgente' => 'sa-badge-red',
    ];

    public function statusLabel(): string
    {
        return self::STATUS_LABELS[$this->status] ?? $this->status;
    }

    public function statusBadgeClass(): string
    {
        return self::STATUS_BADGES[$this->status] ?? 'sa-badge-gray';
    }

    public function priorityLabel(): string
    {
        return self::PRIORITY_LABELS[$this->priority] ?? $this->priority;
    }

    public function priorityBadgeClass(): string
    {
        return self::PRIORITY_BADGES[$this->priority] ?? 'sa-badge-gray';
    }

    protected static function booted(): void
    {
        static::creating(function (Task $task) {
            if ($task->project_id && $project = Project::find($task->project_id)) {
                $task->tenant_id = $task->tenant_id ?: $project->tenant_id;
                $task->user_id = $task->user_id ?: $project->user_id;
            } elseif (! $task->tenant_id) {
                $task->tenant_id = Auth::user()?->tenant_id;
            }

            if (! $task->assigned_to) {
                $task->assigned_to = $task->user_id ?: Auth::id();
            }
        });
    }

    public function scopeTenant($query, ?string $tenantId = null)
    {
        $tenantId = $tenantId ?? config('app.current_tenant_id') ?? Auth::user()?->tenant_id;

        return $tenantId ? $query->where('tenant_id', $tenantId) : $query;
    }

    /** Tâches assignées à un employé donné (ou l'utilisateur connecté par défaut). */
    public function scopeAssignedTo($query, ?int $userId = null)
    {
        return $query->where('assigned_to', $userId ?? Auth::id());
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function activities(): HasMany
    {
        return $this->hasMany(Activity::class)->orderByDesc('activity_date')->orderByDesc('id');
    }

    public function getLoggedMinutesAttribute(): int
    {
        return (int) $this->activities()->sum('duration_minutes');
    }

    public function getIsTimerRunningAttribute(): bool
    {
        return ! is_null($this->timer_started_at);
    }

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

    public function getTimeProgressPercentAttribute(): ?int
    {
        if (! $this->estimated_minutes) {
            return null;
        }

        return (int) min(100, round(($this->logged_minutes / $this->estimated_minutes) * 100));
    }

    /**
     * Mise à jour restreinte utilisée par l'espace employé (Mes tâches) :
     * ne touche jamais aux champs pilotés par l'admin (projet, titre,
     * description, priorité, dates, estimation, assignation).
     */
    public function applyEmployeeUpdate(array $data): void
    {
        $payload = array_intersect_key($data, array_flip(self::EMPLOYEE_EDITABLE_FIELDS));

        if (isset($payload['percent_complete'])) {
            $payload['percent_complete'] = max(0, min(100, (int) $payload['percent_complete']));
        }

        if (($payload['status'] ?? null) === 'terminee') {
            $payload['completed_at'] = Carbon::now();
            $payload['percent_complete'] = 100;
        }

        $this->update($payload);
    }

    public function startTimer(): void
    {
        $this->update([
            'timer_started_at' => Carbon::now(),
            'status' => 'en_cours',
        ]);
    }

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
            'status' => 'validee',
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
