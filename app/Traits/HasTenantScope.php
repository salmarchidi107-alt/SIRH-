<?php

namespace App\Traits;

use App\Scopes\TenantScope;
use Illuminate\Database\Eloquent\Builder;

trait HasTenantScope
{
    protected static function booted()
    {
        static::addGlobalScope(new TenantScope);
    }

    public function scopeWithoutTenantScope(Builder $query)
    {
        $query->withoutGlobalScope(TenantScope::class);
        return $query;
    }

    public function tenant()
    {
        return $this->belongsTo(\App\Models\Tenant::class, 'tenant_id');
    }
}
