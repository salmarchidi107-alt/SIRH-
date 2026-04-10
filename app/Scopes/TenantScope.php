<?php

namespace App\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\Auth;

class TenantScope implements Scope
{
    /**
     * Apply the scope to the Eloquent query builder.
     */
    public function apply(Builder $builder, Model $model): void
    {
        // Superadmin bypass
        if (Auth::check() && Auth::user()->isSuperAdmin()) {
            return;
        }

        $tenantId = config('app.current_tenant_id');
        if (filled($tenantId)) {
            $builder->where($model->getTable() . '.tenant_id', $tenantId);
        }
    }
}
