<?php

namespace App\Http\Middleware;

use App\Models\Domain;
use App\Models\Tenant;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class DomainTenant
{
    /**
     * Resolve tenant from domain and set config('app.current_tenant_id')
     * Runs BEFORE auth middleware
     */
    public function handle(Request $request, Closure $next): Response
    {
        $host = $request->getHost();

        // Skip central domains (superadmin)
        $centralDomains = config('tenancy.central_domains', ['hospitalrh.test', 'localhost', '127.0.0.1']);
        if (in_array($host, $centralDomains)) {
            config(['app.current_tenant_id' => null]);
            return $next($request);
        }

        // Resolve tenant from domain
        $domain = Domain::where('domain', $host)->first();
        if (! $domain || ! $domain->tenant) {
            Log::warning('DomainTenant: No tenant found for domain', ['host' => $host]);
            abort(404, 'Tenant not found');
        }

        config(['app.current_tenant_id' => $domain->tenant_id]);

        return $next($request);
    }
}
