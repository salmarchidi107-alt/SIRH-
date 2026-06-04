<?php

namespace App\Http\Controllers;

use App\Models\VerificationCode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;

/**
 * Gère uniquement l'étape 2 de l'authentification (vérification du code).
 * L'étape 1 (email + mot de passe) reste dans AuthController existant.
 * Le code 2FA est valable tout au long du trimestre (réutilisable à chaque connexion).
 */
class TwoFactorController extends Controller
{
    /**
     * Affiche la page de saisie du code de vérification.
     * Accessible uniquement après l'étape 1 (auth()->check()).
     */
    public function show()
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        if (session('2fa_verified') && session('2fa_user_id') === auth()->id()) {
            return $this->redirectToDashboard();
        }

        return view('auth.otp');
    }

    /**
     * Traite la soumission du code de vérification.
     * Le code reste ASSIGNED tout le trimestre — aucune mutation de statut à la connexion.
     */
    public function verify(Request $request)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $request->validate([
            'code' => ['required', 'string', 'min:4', 'max:20'],
        ]);

        $userId      = auth()->id();
        $throttleKey = '2fa_otp|' . $userId . '|' . $request->ip();

        // Rate limiting : 5 tentatives max sur 10 minutes
        // Protège contre le brute-force uniquement, pas contre les reconnexions légitimes
        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            return redirect()->route('login')->withErrors([
                'email' => "Trop d'erreurs de code. Reconnectez-vous dans {$seconds} secondes.",
            ]);
        }

        // Vérifier que le code est ASSIGNED et appartient à cet utilisateur
        if (!VerificationCode::isValidForUser($request->code, $userId)) {
            RateLimiter::hit($throttleKey, 600); // fenêtre de 10 minutes
            $remaining = 5 - RateLimiter::attempts($throttleKey);
            return back()->withErrors([
                'code' => "Code invalide ou non associé à votre compte." .
                          ($remaining > 0 ? " ({$remaining} tentative(s) restante(s))" : ''),
            ]);
        }

        // ✅ Code valide — statut non modifié, réutilisable jusqu'à la fin du trimestre
        RateLimiter::clear($throttleKey);

        VerificationCode::consume($request->code, $userId);

        // Ouvrir la session 2FA
        session([
            '2fa_verified' => true,
            '2fa_user_id'  => $userId,
        ]);

        return $this->redirectToDashboard();
    }

    /**
     * Redirige vers le bon dashboard selon le rôle.
     * Respecte la logique de redirection déjà en place dans routes/web.php.
     */
    private function redirectToDashboard()
    {
        $user = auth()->user();

        if ($user->isSuperAdmin()) {
            return redirect()->route('superadmin.dashboard');
        }

        if ($user->isEmployee()) {
            return redirect()->route('employee.dashboard');
        }

        return redirect()->route('admin.dashboard');
    }
}
