<?php

namespace App\Http\Controllers;

use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
 use App\Ai\Agents\AssistantRH;
class AuthController extends Controller
{


public function ask(Request $request)
{
    $agent = app(AssistantRH::class);

    $response = $agent->prompt($request->message);

    return response()->json([
        'reply' => $response->text
    ]);
}
    public function showLoginForm()
    {
        try {
            $domain       = request()->getHost();
            $tenantDomain = Domain::where('domain', $domain)->first();
            $tenantData   = null;

            if ($tenantDomain) {
                $tenant = Tenant::find($tenantDomain->tenant_id);

                if ($tenant) {
                    $tenantData = [
                        'name'        => $tenant->name,
                        'brand_color' => $tenant->brand_color ?? '#1a8fa5',
                        'logo_path'   => $tenant->logo_path ?? null,
                    ];
                }
            }

            return view('auth.login', compact('tenantData'));

        } catch (\Throwable $e) {
            // Ne jamais crasher la page de login
            return view('auth.login', ['tenantData' => null]);
        }
    }

    /**
     * Traite la tentative de connexion.
     *
     * Flux :
     *  1. Valider les credentials
     *  2. Auth::attempt() sur le landlord (connexion centrale)
     *  3. Superadmin → redirect direct, pas de tenancy
     *  4. Admin / Employee → résoudre tenant, initialiser tenancy, redirect par rôle
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();


            $user = Auth::user();


            if (!$user->role) {
                $user->role = User::ROLE_EMPLOYEE;
                $user->save();
            }


            if (!$user->employee_id) {
                $employee = Employee::where('email', $user->email)->first();
                if ($employee) {
                    $employee->user_id = $user->id;
                    $employee->save();
                    $user->employee_id = $employee->id;
                    $user->save();
                }
            }

            $defaultRedirect = $user->role === User::ROLE_EMPLOYEE
                ? route('employee.dashboard')
                : route('dashboard');

            return redirect()->intended($defaultRedirect)
                ->with('success', 'Connexion réussie! Bienvenue ' . $user->name);
        }

        $request->session()->regenerate();

        $user = Auth::user();

        // ── Réinitialiser toute tenancy active ────────────────────────────────
        if (tenancy()->initialized) {
            tenancy()->end();
        }

        // ── Superadmin : pas de tenant, redirect direct ───────────────────────
        if ($user->role === 'superadmin') {
            return redirect()->route('superadmin.dashboard');
        }

        // ── Vérifier que l'user a bien un tenant assigné ──────────────────────
        if (! $user->tenant_id) {
            Auth::logout();
            Log::warning('Login sans tenant_id', [
                'email'     => $user->email,
                'role'      => $user->role,
                'tenant_id' => null,
            ]);
            return redirect()->route('login')->withErrors([
                'email' => 'Aucun espace de travail assigné à ce compte. Contactez le super administrateur.',
            ]);
        }

        // ── Résoudre le tenant ────────────────────────────────────────────────
        $tenant = Tenant::find($user->tenant_id);

        if (! $tenant) {
            Auth::logout();
            Log::error('Tenant introuvable lors du login', [
                'email'     => $user->email,
                'tenant_id' => $user->tenant_id,
            ]);
            return redirect()->route('login')->withErrors([
                'email' => 'Espace de travail introuvable. Contactez le super administrateur.',
            ]);
        }

// ── Initialiser la tenancy via service ───────────────────────────────
        app(\App\Services\Auth\PostLoginService::class)->initialize($tenant);

        // ── Redirect par rôle ─────────────────────────────────────────────────
        return match ($user->role) {
            'admin'    => redirect()->route('admin.dashboard')
                            ->with('success', 'Bienvenue, ' . $user->name . ' !'),
            'employee' => redirect()->route('employee.dashboard')
                            ->with('success', 'Bienvenue, ' . $user->name . ' !'),
            default => $this->invalidRole(),
        };
    }

    /**
     * Déconnexion propre avec nettoyage de la tenancy.
     */
    public function logout(Request $request)
    {
        if (tenancy()->initialized) {
            tenancy()->end();
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')
            ->with('success', 'Déconnecté(e) avec succès.');
    }

    /**
     * Rôle non reconnu → déconnexion propre.
     */
    private function invalidRole()
    {
        Auth::logout();

        return redirect()->route('login')->withErrors([
            'email' => 'Rôle utilisateur non reconnu. Contactez le super administrateur.',
        ]);
    }
}
