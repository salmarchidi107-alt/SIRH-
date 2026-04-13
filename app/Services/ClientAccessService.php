<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class ClientAccessService
{
    /**
     * Récupère tous les utilisateurs liés à un tenant (= clients).
     */
    public function getAllClients(): Collection
    {
        return User::whereNotNull('tenant_id')
            ->with('tenant')
            ->orderBy('name')
            ->get();
    }

    /**
     * Modifie l'email et/ou le mot de passe d'un client.
     */
    public function updateClientAccess(User $user, ?string $email, ?string $password): void
    {
        $changes = [];

        if ($email && $email !== $user->email) {
            $changes['email']             = $email;
            $changes['email_verified_at'] = null;
        }

        if ($password) {
            $changes['password'] = Hash::make($password);
        }

        if (!empty($changes)) {
            $user->update($changes);

            Log::info('SuperAdmin updated client access', [
                'super_admin_id' => auth()->id(),
                'client_id'      => $user->id,
                'fields_changed' => array_keys($changes),
            ]);
        }
    }
}
