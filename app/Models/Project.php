<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Auth;
use \App\Traits\HasTenantScope;

class Project extends Model
{
    use HasFactory , HasTenantScope;

    protected $fillable = [
        'tenant_id',
        'user_id',
        'name',
        'description',
        'status',
    ];

    public const STATUSES = ['actif', 'archive'];

    public const STATUS_LABELS = [
        'actif' => 'Actif',
        'archive' => 'Archivé',
    ];

    public const STATUS_BADGES = [
        'actif' => 'badge-ok',
        'archive' => 'badge-gray',
    ];

    public function statusLabel(): string
    {
        return self::STATUS_LABELS[$this->status] ?? $this->status;
    }

    public function statusBadgeClass(): string
    {
        return self::STATUS_BADGES[$this->status] ?? 'badge-gray';
    }

    protected static function booted(): void
    {
        static::creating(function (Project $project) {
            if (! $project->tenant_id) {
                $project->tenant_id = Auth::user()?->tenant_id;
            }
        });
    }

    public function scopeTenant($query, ?string $tenantId = null)
    {
        $tenantId = $tenantId ?? config('app.current_tenant_id') ?? Auth::user()?->tenant_id;

        return $tenantId ? $query->where('tenant_id', $tenantId) : $query;
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class)->orderByRaw(
            "FIELD(status, 'en_cours','en_pause','a_faire','terminee','annulee')"
        )->orderByDesc('updated_at');
    }

    /** Minutes totales enregistrées sur toutes les tâches du projet. */
    public function getLoggedMinutesAttribute(): int
    {
        return (int) Activity::whereIn('task_id', $this->tasks()->pluck('id'))->sum('duration_minutes');
    }

    public function getTasksCountLabelAttribute(): string
    {
        $total = $this->tasks()->count();
        $done = $this->tasks()->where('status', 'terminee')->count();

        return "{$done} / {$total}";
    }
}
