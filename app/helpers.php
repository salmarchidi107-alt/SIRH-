<?php

use App\Models\Tenant;

if (! function_exists('current_tenant_timezone')) {

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
    function tenant_now(): \Carbon\Carbon
    {
        return \Carbon\Carbon::now(current_tenant_timezone());
    }
}
