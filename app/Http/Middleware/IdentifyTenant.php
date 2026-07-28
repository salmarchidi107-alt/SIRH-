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
    public function handle(Request $request, Closure $next): Response
    {
        $currentTenantId = config('app.current_tenant_id');

        // ── Cas 1 : déjà résolu par DomainTenant (domaine tenant réel) ───
        if (filled($currentTenantId)) {
            $tenant = Tenant::find($currentTenantId);
            $this->applyTenantTimezone($tenant);

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
        $this->applyTenantTimezone($tenant);

        // ── Initialiser tenancy si pas encore fait ────────────────────────
        if (! tenancy()->initialized) {
            try {
                tenancy()->initialize($tenant);
            } catch (\Throwable $e) {
                Log::warning('IdentifyTenant : tenancy()->initialize() failed', [
                    'error'     => $e->getMessage(),
                    'tenant_id' => $tenant->id,
                ]);
            }
        }

        return $next($request);
    }

    /**
     * Applique le fuseau horaire propre au tenant pour la durée de cette requête.
     * Ne touche pas au fichier config/app.php : override en mémoire uniquement.
     */
    private function applyTenantTimezone(?Tenant $tenant): void
    {
        if ($tenant && $tenant->timezone) {
            config(['app.timezone' => $tenant->timezone]);
            date_default_timezone_set($tenant->timezone);
        }
    }
}
