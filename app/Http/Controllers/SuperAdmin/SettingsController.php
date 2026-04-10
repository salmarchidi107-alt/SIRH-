<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Http\Requests\SuperAdmin\UpdateClientAccessRequest;
use App\Models\Plan;
use App\Models\User;
use App\Services\ClientAccessService;
use App\Settings\GlobalSettings;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class SettingsController extends Controller
{
    public function __construct(
        protected ClientAccessService $clientAccessService,
        protected GlobalSettings      $globalSettings,   // ← injection directe
    ) {}

    /**
     * Affiche la page des paramètres
     */
    public function index(): View
    {
        $plans = Plan::orderBy('prix_mensuel')->get();

        $parametres = [
            'mode_maintenance'    => (bool) $this->globalSettings->mode_maintenance,
            'notifications_email' => (bool) $this->globalSettings->notifications_email,
            'email_support'       => $this->globalSettings->email_support,
            'nom_plateforme'      => $this->globalSettings->nom_plateforme,
        ];

        $clients = $this->clientAccessService->getAllClients();

        return view('superadmin.settings.index', compact('plans', 'parametres', 'clients'));
    }

    /**
     * Met à jour un plan (max_utilisateurs, duree_essai, prix)
     */
    public function updatePlan(Request $request, Plan $plan): RedirectResponse
    {
        $validated = $request->validate([
            'max_utilisateurs'  => 'nullable|integer|min:1',
            'duree_essai_jours' => 'required|integer|min:1|max:365',
            'prix_mensuel'      => 'required|numeric|min:0',
        ]);

        $plan->update($validated);

        return back()->with('success', "Plan « {$plan->nom} » mis à jour avec succès.");
    }

    /**
     * Met à jour les paramètres globaux de la plateforme.
     *
     * CORRECTION : avec Spatie, on assigne propriété par propriété
     * car fill() n'accepte que les propriétés déjà en base.
     */
    public function updateGlobal(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'mode_maintenance'    => 'nullable|boolean',
            'notifications_email' => 'nullable|boolean',
            'email_support'       => 'required|email|max:255',
            'nom_plateforme'      => 'required|string|max:100',
        ]);

        // ── Assignation propriété par propriété (méthode sûre avec Spatie) ──
        $this->globalSettings->mode_maintenance    = (bool) ($validated['mode_maintenance']    ?? false);
        $this->globalSettings->notifications_email = (bool) ($validated['notifications_email'] ?? false);
        $this->globalSettings->email_support       = $validated['email_support'];
        $this->globalSettings->nom_plateforme      = $validated['nom_plateforme'];
        $this->globalSettings->save();

        Cache::forget('parametres_globaux');

        return back()->with('success', 'Paramètres globaux enregistrés.');
    }

    /**
     * Modifier l'email et/ou le mot de passe d'un client
     */
    public function updateClientAccess(UpdateClientAccessRequest $request, User $user): RedirectResponse
    {
        $this->clientAccessService->updateClientAccess(
            user:     $user,
            email:    $request->validated('email'),
            password: $request->validated('password'),
        );

        return back()->with('success', "Accès de « {$user->name} » mis à jour avec succès.");
    }
}
