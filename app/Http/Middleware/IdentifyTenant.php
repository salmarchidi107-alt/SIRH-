<?php

namespace App\Http\Middleware;

use App\Models\Tenant;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class IdentifyTenant
{
    /**
     * Initialise la tenancy depuis l'user connecté.
     *
     * FIX : Sur localhost, DomainTenant ne set pas current_tenant_id (null).
     * Ce middleware DOIT le setter depuis $user->tenant_id dans ce cas.
     *
     * Priorité :
     *  1. current_tenant_id déjà set (domaine tenant réel) → passer
     *  2. Superadmin ou pas de tenant_id                  → passer
     *  3. User connecté avec tenant_id valide             → setter config et passer
     *  4. Tenant introuvable                              → déconnexion
     *  5. Non connecté                                    → passer
     */
    public function handle(Request $request, Closure $next): Response
    {
        $currentTenantId = config('app.current_tenant_id');

        // ── Cas 1 : déjà résolu par DomainTenant (domaine tenant réel) ───
        if (filled($currentTenantId)) {
            return $next($request);
        }

        // ── Pas connecté → passer (login page, etc.) ─────────────────────
        if (! Auth::check()) {
            return $next($request);
        }

        $user = Auth::user();

        // ── Cas 2 : Superadmin ou sans tenant ────────────────────────────
        if ($user->isSuperAdmin() || is_null($user->tenant_id)) {
            config(['app.current_tenant_id' => null]);
            return $next($request);
        }

        // ── Cas 3 : User avec tenant_id → résoudre et setter ─────────────
        // FIX PRINCIPAL : on set TOUJOURS depuis l'user sur localhost
        // (quand DomainTenant n'a pas pu résoudre depuis le domaine)
        $tenant = Tenant::find($user->tenant_id);

        if (! $tenant) {
            Log::warning('IdentifyTenant : tenant introuvable', [
                'user_id'   => $user->id,
                'email'     => $user->email,
                'tenant_id' => $user->tenant_id,
            ]);

            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')
                ->withErrors(['email' => 'Espace de travail introuvable. Contactez le super administrateur.']);
        }

        // ── Setter current_tenant_id en config ────────────────────────────
        config(['app.current_tenant_id' => (string) $tenant->id]);

        // ── Initialiser tenancy si pas encore fait ────────────────────────
        if (! tenancy()->initialized) {
            try {
                tenancy()->initialize($tenant);
            } catch (\Throwable $e) {
                Log::warning('IdentifyTenant : tenancy()->initialize() failed', [
                    'error'     => $e->getMessage(),
                    'tenant_id' => $tenant->id,
                ]);
                // Ne pas bloquer — la config est setée, ça suffit pour les requêtes simples
            }
        }

        return $next($request);
    }
}
