<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Collection;

class ClientAccessService
{
    /**
     * Retourne tous les utilisateurs groupés par tenant_id
     */
    public function getAllClients(): Collection
    {
        $columns = ['id', 'name', 'email', 'role', 'tenant_id'];

        if (Schema::hasColumn('users', 'first_name'))     $columns[] = 'first_name';
        if (Schema::hasColumn('users', 'last_name'))      $columns[] = 'last_name';
        if (Schema::hasColumn('users', 'plain_password')) $columns[] = 'plain_password';

        return User::with('tenant')
            ->whereNotNull('tenant_id')
            ->select($columns)
            ->orderBy('tenant_id')
            ->orderBy('name')
            ->get()
            ->groupBy('tenant_id');
    }

    /**
     * Met à jour email et/ou mot de passe d'un utilisateur
     * Le chiffrement de plain_password est géré automatiquement par le Model
     */
    public function updateClientAccess(User $user, ?string $email, ?string $password): void
    {
        $updates = [];

        if (!empty($email)) {
            $updates['email'] = $email;
        }

        if (!empty($password)) {
            $updates['password'] = Hash::make($password);

            if (Schema::hasColumn('users', 'plain_password')) {
                // Le setter du Model chiffre automatiquement
                $updates['plain_password'] = $password;
            }
        }

        if (!empty($updates)) {
            $user->update($updates);
        }
    }
}
