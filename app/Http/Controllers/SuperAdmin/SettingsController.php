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

    public function index(): View
    {
        // getAllClients() retourne déjà les données groupées par tenant
        // plain_password est déchiffré automatiquement par le getter du Model
        $clients = $this->clientAccessService->getAllClients();

        return view('superadmin.settings.index', compact('clients'));
    }

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
