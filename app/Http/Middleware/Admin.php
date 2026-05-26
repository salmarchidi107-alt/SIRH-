<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class Admin
{
    /**
     * Handle an incoming request.
     *
     * Autorise : admin, rh, superadmin
     * Bloque   : employee + utilisateurs d'un autre tenant
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! auth()->check()) {
            return redirect()->route('login');
        }

        $user            = auth()->user();
        $currentTenantId = config('app.current_tenant_id');

        // ── Vérification du rôle ─────────────────────────────────────────
        if (! $user->isAdminOrRh() && ! $user->isSuperAdmin()) {
            abort(403, 'Accès réservé aux administrateurs et responsables RH.');
        }

        // ── Vérification du tenant ───────────────────────────────────────
        // CORRECTION : cast les deux en string pour éviter "1" !== 1
        if (
            filled($currentTenantId) &&
            ! $user->isSuperAdmin() &&
            (string) $user->tenant_id !== (string) $currentTenantId
        ) {
            abort(403, 'Utilisateur non autorisé sur ce tenant.');
        }

        return $next($request);
    }
}
