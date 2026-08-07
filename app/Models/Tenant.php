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

    /**
     * IMPORTANT (Stancl Tenancy) : toute colonne absente de cette liste est
     * automatiquement redirigée vers la colonne JSON `data` au lieu de sa
     * vraie colonne SQL, même si $fillable l'autorise. `timezone` doit donc
     * impérativement figurer ici pour être écrit dans la vraie colonne
     * `timezone` de la table `tenants`.
     */
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
            'timezone',
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
