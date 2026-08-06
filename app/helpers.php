<?php

use App\Models\Tenant;

if (! function_exists('current_tenant_timezone')) {
    /**
     * Résout le fuseau horaire du tenant courant, quel que soit le contexte
     * (session admin classique, ou session badge employé).
     *
     * Fallback sur Africa/Casablanca si aucun tenant n'est résolvable ou
     * si son timezone n'est pas configuré.
     */
    function current_tenant_timezone(): string
    {
        $tenantId = config('app.current_tenant_id')
            ?? auth('badge')->user()?->tenant_id
            ?? auth()->user()?->tenant_id;

        if (blank($tenantId)) {
            return 'Africa/Casablanca';
        }

        $timezone = Tenant::where('id', $tenantId)->value('timezone');

        if (blank($timezone) || ! in_array($timezone, \DateTimeZone::listIdentifiers(), true)) {
            return 'Africa/Casablanca';
        }

        return $timezone;
    }
}

if (! function_exists('tenant_now')) {
    /**
     * Raccourci : Carbon::now() dans le fuseau du tenant courant.
     */
    function tenant_now(): \Carbon\Carbon
    {
        return \Carbon\Carbon::now(current_tenant_timezone());
    }
}