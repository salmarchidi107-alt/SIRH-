<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Vérifie que l'utilisateur a complété l'étape 2FA dans la session en cours.
 *
 * À appliquer APRÈS le middleware 'auth' sur toutes les routes protégées.
 * Ne pas appliquer sur /verify et /logout.
 */
class Require2FA
{
    public function handle(Request $request, Closure $next): Response
    {
        // Doit être authentifié
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        // Doit avoir validé le code 2FA
        if (!session('2fa_verified')) {
            return redirect()->route('2fa.show');
        }

        // Cohérence : le user en session 2FA doit être le même que le user connecté
        // (protection contre la réutilisation de session après changement de compte)
        if (session('2fa_user_id') !== auth()->id()) {
            auth()->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            return redirect()->route('login')
                             ->withErrors(['email' => 'Session invalide. Reconnectez-vous.']);
        }

        return $next($request);
    }
}
