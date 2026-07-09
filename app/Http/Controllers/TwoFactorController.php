<?php

namespace App\Http\Controllers;

use App\Models\VerificationCode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;

/**
 * Gère uniquement l'étape 2 de l'authentification (vérification du code).
 * L'étape 1 (email + mot de passe) reste dans AuthController existant.
 * Le code 2FA est permanent et réutilisable jusqu'à révocation ou remplacement.
 */
class TwoFactorController extends Controller
{
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

        if (!$record) {
            RateLimiter::hit($throttleKey, 600);
            $remaining = 5 - RateLimiter::attempts($throttleKey);
            return back()->withErrors([
                'code' => "Code invalide ou non associé à votre compte." .
                          ($remaining > 0 ? " ({$remaining} tentative(s) restante(s))" : ''),
            ]);
        }

        if ($record->status !== VerificationCode::STATUS_ASSIGNED) {
            RateLimiter::hit($throttleKey, 600);
            return back()->withErrors([
                'code' => "Ce code n'est plus valide (révoqué ou remplacé). " .
                          "Contactez votre Super Admin pour obtenir un nouveau code.",
            ]);
        }

        // ✅ Code valide — statut non modifié, réutilisable indéfiniment
        RateLimiter::clear($throttleKey);

        VerificationCode::consume($request->code, $userId);

        session([
            '2fa_verified' => true,
            '2fa_user_id'  => $userId,
        ]);

        return $this->redirectToDashboard();
    }

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
