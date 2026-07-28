<?php

namespace App\Models;

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
        'sector',
        'region',
        'address',
        'phone',
        'ice',
        'email_societe',
        'website',
        'logo_path',
        'brand_color',
        'sidebar_color',
        'timezone',
        'database_name',
    ];

    public static function getCustomColumns(): array
    {
        return [
            'id',
            'name',
            'sector',
            'region',
            'address',
            'phone',
            'ice',
            'email_societe',
            'website',
            'logo_path',
            'brand_color',
            'sidebar_color',
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
        return $this->hasOne(User::class, 'tenant_id')->where('role', 'admin');
    }


// ─── Accessors ────────────────────────────────────────────────────────────

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

    public function getUsersCountAttribute(): int
    {
        return $this->users()->count();
    }
}
