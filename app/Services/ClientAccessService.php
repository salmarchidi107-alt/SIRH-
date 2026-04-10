<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class ClientAccessService
{
    /**
     * Récupérer tous les utilisateurs liés à un tenant.
     * Dans votre architecture : tenant_id NOT NULL = client.
     */
    public function getAllClients(): Collection
    {
        return User::whereNotNull('tenant_id')
            ->with('tenant')               // eager-load le tenant lié
            ->orderBy('name')
            ->get();
    }

    /**
     * Mettre à jour l'email et/ou le mot de passe d'un client.
     *
     * @param User        $user     L'utilisateur à modifier
     * @param string|null $email    Nouvel email (null = pas de changement)
     * @param string|null $password Nouveau mot de passe en clair (null = pas de changement)
     */
    public function updateClientAccess(User $user, ?string $email, ?string $password): void
    {
        $changes = [];

        if ($email && $email !== $user->email) {
            $changes['email']             = $email;
            $changes['email_verified_at'] = null; // invalide la vérification précédente
        }

        if ($password) {
            $changes['password'] = Hash::make($password);
        }

        if (!empty($changes)) {
            $user->update($changes);

            Log::info('SuperAdmin: accès client modifié', [
                'super_admin_id'    => auth()->id(),
                'super_admin_email' => auth()->user()->email,
                'client_id'         => $user->id,
                'client_email'      => $user->email,
                'champs_modifiés'   => array_keys($changes),
            ]);
        }
    }
}
