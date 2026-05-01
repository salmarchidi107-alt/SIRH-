<?php

namespace App\Traits;

use App\Scopes\TenantScope;
use Illuminate\Database\Eloquent\Builder;

trait HasTenantScope
{
    protected static function booted(): void
    {
        static::addGlobalScope(new TenantScope);

        static::creating(function ($model) {
            if (is_null($model->tenant_id)) {
                // Priorité 1 : config définie par le middleware
                $tenantId = config('app.current_tenant_id');

                // Priorité 2 : fallback direct sur l'user connecté
                if (blank($tenantId) && auth()->check()) {
                    $tenantId = auth()->user()->tenant_id;
                }

                $model->tenant_id = $tenantId;
            }
        });
    }

    public function scopeWithoutTenantScope(Builder $query): Builder
    {
        return $query->withoutGlobalScope(TenantScope::class);
    }

    public function tenant()
    {
        return $this->belongsTo(\App\Models\Tenant::class, 'tenant_id');
    }
}