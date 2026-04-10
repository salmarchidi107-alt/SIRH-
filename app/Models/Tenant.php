<?php

namespace App\Models;

use App\Enums\TenantPlan;
use App\Enums\TenantStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Stancl\Tenancy\Database\Models\Tenant as BaseTenant;
use Stancl\Tenancy\Database\Concerns\HasDomains;

class Tenant extends BaseTenant
{
    use HasFactory, HasDomains;

    public $incrementing = false;
    protected $keyType   = 'string';

    protected $fillable = [
        'id',
        'name',
        'slug',
        'sector',
        'logo_path',
        'brand_color',
        'plan',
        'status',
        'region',
        'database_name',
    ];

    protected $casts = [
        'plan'   => TenantPlan::class,
        'status' => TenantStatus::class,
    ];

    // ✅ Obligatoire : dire à stancl que ces colonnes sont réelles
    // Sans ça, stancl les met dans data[] et rien ne persiste
    public static function getCustomColumns(): array
    {
        return [
            'id',
            'name',
            'slug',
            'sector',
            'logo_path',
            'brand_color',
            'plan',
            'status',
            'region',
            'database_name',
        ];
    }

    // ─── Relations ────────────────────────────────────────────────────────────

    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'tenant_id');
    }

    public function admin(): HasOne
    {
        return $this->hasOne(User::class, 'tenant_id')
                    ->where('role', 'admin');
    }

    // ─── Scopes ───────────────────────────────────────────────────────────────

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    // ─── Accessors ────────────────────────────────────────────────────────────

    public function getDomainAttribute(): string
    {
        return ($this->slug ?? '') . '.hospitalrh.test';
    }

    public function getInitialsAttribute(): string
    {
        $words = array_filter(explode(' ', $this->name ?? ''));
        $ini   = implode('', array_map(
            fn($w) => strtoupper($w[0]),
            array_slice(array_values($words), 0, 2)
        ));
        return $ini ?: '?';
    }

    public function getStorageUsageAttribute(): int { return 0; }
    public function getApiUsageAttribute(): int     { return 0; }
}
