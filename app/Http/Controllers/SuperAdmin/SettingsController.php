<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Http\Requests\SuperAdmin\UpdateClientAccessRequest;
use App\Models\User;
use App\Services\ClientAccessService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class SettingsController extends Controller
{
    public function __construct(
        protected ClientAccessService $clientAccessService,
    ) {}

    /**
     * Affiche la page des paramètres — Accès Clients
     * Les utilisateurs sont chargés avec leur tenant et leurs rôles,
     * puis groupés côté Blade par tenant_id.
     */
    public function index(): View
    {
        $clients = $this->clientAccessService->getAllClients();
// dd($clients);
        return view('superadmin.settings.index', compact('clients'));
    }

    /**
     * Modifier l'email et/ou le mot de passe d'un client
     */
    public function updateClientAccess(
        UpdateClientAccessRequest $request,
        User $user
    ): RedirectResponse {
        $this->clientAccessService->updateClientAccess(
            user:     $user,
            email:    $request->validated('email'),
            password: $request->validated('password'),
        );

        return back()->with('success', "Accès de « {$user->name} » mis à jour avec succès.");
    }
}
