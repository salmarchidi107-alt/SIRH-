<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

class Activity extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'task_id',
        'user_id',
        'type',
        'activity_date',
        'start_time',
        'end_time',
        'duration_minutes',
        'comment',
        'status',
        'attachment_path',
    ];

    protected $casts = [
        'activity_date' => 'date',
    ];

    public const STATUSES = ['soumise', 'validee', 'rejetee'];

    public const STATUS_LABELS = [
        'soumise' => 'Soumise',
        'validee' => 'Validée',
        'rejetee' => 'Rejetée',
    ];

    public const STATUS_BADGES = [
        'soumise' => 'sa-badge-amber',
        'validee' => 'sa-badge-green',
        'rejetee' => 'sa-badge-red',
    ];

    public function statusLabel(): string
    {
        return self::STATUS_LABELS[$this->status] ?? $this->status;
    }

    public function statusBadgeClass(): string
    {
        return self::STATUS_BADGES[$this->status] ?? 'sa-badge-gray';
    }

    protected static function booted(): void
    {
        static::creating(function (Activity $activity) {
            if (! $activity->tenant_id) {
                $activity->tenant_id = Auth::user()?->tenant_id;
            }
            if (! $activity->status) {
                $activity->status = 'soumise';
            }
        });
    }

    public function scopeTenant($query, ?string $tenantId = null)
    {
        $tenantId = $tenantId ?? config('app.current_tenant_id') ?? Auth::user()?->tenant_id;

        return $tenantId ? $query->where('tenant_id', $tenantId) : $query;
    }

    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
