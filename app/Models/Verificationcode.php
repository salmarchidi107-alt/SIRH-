<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use \App\Traits\HasTenantScope;

class VerificationCode extends Model
{
        use HasTenantScope;

    // ─── Statuts ──────────────────────────────────────────────────────────────

    const STATUS_ASSIGNED = 'assigned';
    const STATUS_USED     = 'used';
    const STATUS_REVOKED  = 'revoked';

    const TERMINAL_STATUSES = [self::STATUS_USED, self::STATUS_REVOKED];

    private const TRANSITIONS = [
        self::STATUS_ASSIGNED => [self::STATUS_USED, self::STATUS_REVOKED],
        self::STATUS_USED     => [],
        self::STATUS_REVOKED  => [],
    ];

    // ─── Fillable / Casts ────────────────────────────────────────────────────

    protected $fillable = [
        'code',
        'tenant_id',
        'status',
        'user_id',
        'assigned_by',
        'assigned_at',
        'used_at',
        'revoked_by',
        'revoked_at',
        'revoke_reason',
        'generated_by',
    ];

    protected $casts = [
        'assigned_at' => 'datetime',
        'used_at'     => 'datetime',
        'revoked_at'  => 'datetime',
    ];

    // ─── Relations ───────────────────────────────────────────────────────────

    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function assignedBy(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    public function revokedBy(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'revoked_by');
    }

    public function generatedBy(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'generated_by');
    }

    public function tenant(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    // ─── Scopes ──────────────────────────────────────────────────────────────

    public function scopeAssigned(Builder $q): Builder
    {
        return $q->where('status', self::STATUS_ASSIGNED);
    }

    public function scopeActive(Builder $q): Builder
    {
        return $q->where('status', self::STATUS_ASSIGNED);
    }

    public function scopeForTenant(Builder $q, string $tenantId): Builder
    {
        return $q->where('tenant_id', $tenantId);
    }

    public function scopeForUser(Builder $q, int $userId): Builder
    {
        return $q->where('user_id', $userId);
    }

    /** Codes utilisés au moins une fois (status ASSIGNED + used_at non null). */
    public function scopeUsedAtLeastOnce(Builder $q): Builder
    {
        return $q->where('status', self::STATUS_ASSIGNED)
                 ->whereNotNull('used_at');
    }

    /** Codes jamais utilisés (status ASSIGNED + used_at null). */
    public function scopeNeverUsed(Builder $q): Builder
    {
        return $q->where('status', self::STATUS_ASSIGNED)
                 ->whereNull('used_at');
    }

    // ─── Helpers d'état ──────────────────────────────────────────────────────

    public function hasBeenUsed(): bool
    {
        return !is_null($this->used_at);
    }

    // ─── Machine à états ─────────────────────────────────────────────────────

    public function canTransitionTo(string $status): bool
    {
        return in_array($status, self::TRANSITIONS[$this->status] ?? []);
    }

    public function isTerminal(): bool
    {
        return in_array($this->status, self::TERMINAL_STATUSES);
    }

    // ─── Transitions ─────────────────────────────────────────────────────────

    public function revoke(int $revokedBy, string $reason = ''): void
    {
        if (!$this->canTransitionTo(self::STATUS_REVOKED)) {
            throw new \DomainException(
                "Impossible de révoquer un code en statut [{$this->status}]."
            );
        }

        $this->update([
            'status'        => self::STATUS_REVOKED,
            'revoked_by'    => $revokedBy,
            'revoked_at'    => now(),
            'revoke_reason' => $reason,
        ]);
    }

    public function markUsed(): void
    {
        if (!$this->canTransitionTo(self::STATUS_USED)) {
            throw new \DomainException(
                "Impossible de marquer en statut USED un code en [{$this->status}]."
            );
        }

        $this->update([
            'status'  => self::STATUS_USED,
            'used_at' => now(),
        ]);
    }

    // ─── Méthodes statiques (TwoFactorController) ────────────────────────────

    /**
     * Vérifie qu'un code existe, est ASSIGNED, et appartient à cet utilisateur.
     * Aucune notion de trimestre : le code est valable tant qu'il n'est pas
     * révoqué ou remplacé.
     */
    public static function isValidForUser(string $code, int $userId): bool
    {
        return static::where('code', $code)
            ->where('user_id', $userId)
            ->where('status', self::STATUS_ASSIGNED)
            ->exists();
    }

    /**
     * Le code est réutilisable indéfiniment. Le statut reste ASSIGNED,
     * used_at est simplement mis à jour à chaque utilisation.
     */
    public static function consume(string $code, int $userId): void
    {
        $record = static::where('code', $code)
            ->where('user_id', $userId)
            ->where('status', self::STATUS_ASSIGNED)
            ->firstOrFail();

        $record->update(['used_at' => now()]);
    }

    // ─── Génération de code unique ────────────────────────────────────────────

    public static function generateUniqueCode(string $tenantId, int $maxAttempts = 100): string
{
    for ($i = 0; $i < $maxAttempts; $i++) {
        $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        if (!static::withoutTenantScope()
                ->where('code', $code)
                ->where('tenant_id', $tenantId)
                ->exists()) {
            return $code;
        }
    }

    throw new \RuntimeException("Impossible de générer un code unique après {$maxAttempts} tentatives.");
}


    }
