<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Http\Requests\SuperAdmin\UpdateClientAccessRequest;
use App\Models\User;
use App\Services\ClientAccessService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class SettingsController extends Controller
{
    public function __construct(
        protected ClientAccessService $clientAccessService,
    ) {}

    public function index(): View
    {
        // Récupérer directement avec plain_password sans passer par le service
        // pour s'assurer que la colonne est bien chargée
        $columns = ['id', 'name', 'email', 'role', 'tenant_id'];

        if (Schema::hasColumn('users', 'first_name'))     $columns[] = 'first_name';
        if (Schema::hasColumn('users', 'last_name'))      $columns[] = 'last_name';
        if (Schema::hasColumn('users', 'plain_password')) $columns[] = 'plain_password';

        $clients = User::with('tenant')
            ->whereNotNull('tenant_id')
            ->select($columns)
            ->orderBy('tenant_id')
            ->orderBy('name')
            ->get()
            ->groupBy('tenant_id');

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
