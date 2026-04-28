<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class Admin
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

        if (! $user->isAdminOrRh()) {
            abort(403, 'Accès réservé aux administrateurs du tenant.');
        }

        if (filled($currentTenantId) && $user->tenant_id !== $currentTenantId) {
            abort(403, 'Utilisateur non autorisé sur ce tenant.');
        }

        return $next($request);
    }
}
