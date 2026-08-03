<?php

namespace App\Http\Controllers;

use App\Ai\Agents\AssistantRH;
use App\Models\User;
use App\Services\Auth\LoginService;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function __construct(
        private LoginService $loginService,
    ) {}

    public function ask(Request $request)
    {
        $agent    = app(AssistantRH::class);
        $response = $agent->prompt($request->message);

        return response()->json([
            'reply' => $response->text,
        ]);
    }

    public function showLoginForm()
    {
        $tenantData = $this->loginService->getTenantBrandingForCurrentDomain();

        return view('auth.login', compact('tenantData'));
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (! $this->loginService->attempt($credentials)) {
            return redirect()->back()
                ->withErrors(['email' => 'Les identifiants fournis sont incorrects.']);
        }

        $request->session()->regenerate();

        $user = $this->loginService->prepareAuthenticatedUser();

        if ($user->isSuperAdmin()) {
            return redirect()->route('superadmin.dashboard');
        }

        $request->session()->forget('2fa_verified');

        return redirect()->route('2fa.show');
    }

    public function logout(Request $request)
    {
        $this->loginService->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')
            ->with('success', 'Déconnecté(e) avec succès.');
    }
}
