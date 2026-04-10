<?php

namespace App\Http\Middleware;

use App\Models\Tenant;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class IdentifyTenant
{
    /**
     * Initialise la tenancy depuis l'user connecté.
     *
     * Priorité :
     *  1. Tenancy déjà initialisée (ex: post-login)  → on expose l'ID en config et on passe
     *  2. Superadmin ou pas de tenant_id             → on passe directement
     *  3. User connecté avec tenant_id valide        → on initialise la tenancy
     *  4. Tenant introuvable                         → déconnexion propre
     *  5. Non connecté (page login, etc.)            → on passe
     */
    public function handle(Request $request, Closure $next): Response
    {
        $currentTenantId = config('app.current_tenant_id');

        // Already set by DomainTenant middleware or superadmin (null)
        if (filled($currentTenantId)) {
            return $next($request);
        }

        // User logged in but no domain tenant - fallback to user tenant_id (legacy)
        if (\Illuminate\Support\Facades\Auth::check()) {
            $user = \Illuminate\Support\Facades\Auth::user();
            if ($user->role === 'superadmin' || is_null($user->tenant_id)) {
                config(['app.current_tenant_id' => null]);
                return $next($request);
            }

            $tenant = Tenant::find($user->tenant_id);
            if (! $tenant) {
                Log::warning('IdentifyTenant : tenant introuvable', [
                    'user_id'   => $user->id,
                    'email'     => $user->email,
                    'tenant_id' => $user->tenant_id,
                ]);

                \Illuminate\Support\Facades\Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return redirect()->route('login')
                    ->withErrors(['email' => 'Espace de travail introuvable. Contactez le super administrateur.']);
            }

            config(['app.current_tenant_id' => $tenant->id]);
        }

        return $next($request);
    }
}
