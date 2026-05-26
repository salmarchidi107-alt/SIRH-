<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Authenticated Middleware (ex TenantUser)
 *
 * Vérifie que l'utilisateur connecté appartient au tenant courant.
 *
 * FIX : Sur localhost, config('app.current_tenant_id') peut être null
 * (DomainTenant ne set rien pour localhost).
 * Dans ce cas on vérifie depuis $user->tenant_id directement.
 */
class Authenticated
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! Auth::check()) {
            return redirect()->route('login');
        }

        $user            = Auth::user();
        $currentTenantId = config('app.current_tenant_id');

        // Superadmin : accès total sans vérification tenant
        if ($user->isSuperAdmin()) {
            return $next($request);
        }

        // ── Si current_tenant_id est null (localhost / dev) ───────────────
        // On ne peut pas vérifier l'appartenance tenant via config.
        // On fait confiance à l'user connecté (IdentifyTenant a déjà validé).
        if (is_null($currentTenantId)) {
            // L'user doit quand même avoir un tenant_id (sauf superadmin déjà géré)
            if (is_null($user->tenant_id)) {
                abort(403, 'Aucun espace de travail assigné.');
            }
            return $next($request);
        }

        // ── current_tenant_id set (production / domaine réel) ─────────────
        // Vérifier que l'user appartient bien à ce tenant
        if ((string) $user->tenant_id !== (string) $currentTenantId) {
            abort(403, 'Utilisateur non autorisé sur ce tenant.');
        }

        return $next($request);
    }
}
