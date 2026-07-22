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
        'duration_minutes',
        'comment',
        'attachment_path',
    ];

    protected $casts = [
        'activity_date' => 'date',
    ];

    protected static function booted(): void
    {
        static::creating(function (Activity $activity) {
            if (! $activity->tenant_id) {
                $activity->tenant_id = Auth::user()?->tenant_id;
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
