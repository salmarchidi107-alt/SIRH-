<?php

namespace App\Http\Controllers;

use App\Services\Auth\TwoFactorService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;


class TwoFactorController extends Controller
{
    public function __construct(
        private TwoFactorService $twoFactorService,
    ) {}

    public function show()
    {
        if (! Auth::check()) {
            return redirect()->route('login');
        }

        if ($this->twoFactorService->isAlreadyVerified(auth()->id())) {
            return $this->redirectToDashboard();
        }

        return view('auth.otp');
    }

    public function verify(Request $request)
    {
        if (! Auth::check()) {
            return redirect()->route('login');
        }

        $request->validate([
            'code' => ['required', 'string', 'min:4', 'max:20'],
        ]);

        $userId = auth()->id();
        $ip     = $request->ip();

        $lockoutSeconds = $this->twoFactorService->getLockoutSecondsRemaining($userId, $ip);

        if ($lockoutSeconds !== null) {
            $this->twoFactorService->lockoutLogout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->withErrors([
                'email' => "Trop d'erreurs de code. Reconnectez-vous dans {$lockoutSeconds} secondes.",
            ]);
        }

        $result = $this->twoFactorService->verifyCode($userId, $ip, $request->code);

        return match ($result['result']) {
            'invalid' => back()->withErrors([
                'code' => 'Code invalide ou non associé à votre compte.' .
                          ($result['remaining'] > 0 ? " ({$result['remaining']} tentative(s) restante(s))" : ''),
            ]),

            'revoked' => back()->withErrors([
                'code' => "Ce code n'est plus valide (révoqué ou remplacé). " .
                          'Contactez votre Super Admin pour obtenir un nouveau code.',
            ]),

            'no_tenant' => redirect()->route('login')->withErrors([
                'email' => 'Aucun espace de travail assigné à ce compte. Contactez le super administrateur.',
            ]),

            'tenant_not_found' => redirect()->route('login')->withErrors([
                'email' => 'Espace de travail introuvable. Contactez le super administrateur.',
            ]),

            default => $this->redirectToDashboard(),
        };
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
