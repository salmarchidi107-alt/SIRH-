<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class Employee
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        $user = auth()->user();
        $currentTenantId = config('app.current_tenant_id');

        if (! $user->isEmployee()) {
            abort(403, 'Espace réservé aux employés du tenant.');
        }

        if (filled($currentTenantId) && $user->tenant_id !== $currentTenantId) {
            abort(403, 'Utilisateur non autorisé sur ce tenant.');
        }

        return $next($request);
    }
}
