<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class ClientAccessService
{
    /**
     * Récupère tous les utilisateurs liés à un tenant existant,
     * avec leur tenant chargé en eager loading.
     *
     * ✅ CORRECTION BUG 2 :
     *   - whereHas('tenant')  → exclut les tenant_id orphelins (tenant supprimé)
     *     en remplacement de whereNotNull('tenant_id') qui laissait passer les FK mortes
     *   - ->groupBy('tenant_id') → groupe la collection par tenant pour la vue Blade,
     *     évitant l'affichage "Sans tenant"
     */
    public function getAllClients(): Collection
    {
        return User::whereHas('tenant')
            ->with('tenant')
            ->orderBy('name')
            ->get()
            ->groupBy('tenant_id');
    }

    /**
     * Modifie l'email et/ou le mot de passe d'un client.
     * Ne met à jour que les champs fournis (non null).
     * Réinitialise email_verified_at si l'email change.
     */
    public function updateClientAccess(
        User    $user,
        ?string $email,
        ?string $password
    ): void {
        $changes = [];

        if ($email && $email !== $user->email) {
            $changes['email']             = $email;
            $changes['email_verified_at'] = null;
        }

        if ($password) {
            $changes['password'] = Hash::make($password);
        }

        if (! empty($changes)) {
            $user->update($changes);

            Log::info('SuperAdmin updated client access', [
                'super_admin_id' => auth()->id(),
                'client_id'      => $user->id,
                'fields_changed' => array_keys($changes),
            ]);
        }
    }
}
