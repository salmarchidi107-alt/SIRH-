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
     * Le code reste ASSIGNED tout le trimestre — aucune mutation de statut à la connexion,
     * sauf s'il s'avère appartenir à un trimestre révolu (expiration "à la volée").
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
        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            return redirect()->route('login')->withErrors([
                'email' => "Trop d'erreurs de code. Reconnectez-vous dans {$seconds} secondes.",
            ]);
        }

        $record = VerificationCode::where('code', $request->code)
            ->where('user_id', $userId)
            ->first();

        // Code inexistant ou n'appartenant pas à cet utilisateur
        if (!$record) {
            RateLimiter::hit($throttleKey, 600);
            $remaining = 5 - RateLimiter::attempts($throttleKey);
            return back()->withErrors([
                'code' => "Code invalide ou non associé à votre compte." .
                          ($remaining > 0 ? " ({$remaining} tentative(s) restante(s))" : ''),
            ]);
        }

        // Code d'un trimestre révolu → expiration "à la volée" + refus d'accès
        // (filet de sécurité si le job planifié verification-codes:expire n'est pas encore passé)
        if ($record->status === VerificationCode::STATUS_ASSIGNED
            && $record->quarter !== VerificationCode::currentQuarterLabel()) {
            $record->expire();
        }

        if ($record->status === VerificationCode::STATUS_EXPIRED) {
            RateLimiter::hit($throttleKey, 600);
            return back()->withErrors([
                'code' => "Ce code a expiré à la fin du trimestre précédent. " .
                          "Contactez votre Super Admin pour obtenir un nouveau code.",
            ]);
        }

        if ($record->status !== VerificationCode::STATUS_ASSIGNED) {
            RateLimiter::hit($throttleKey, 600);
            return back()->withErrors([
                'code' => "Ce code n'est plus valide (révoqué ou déjà utilisé).",
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
