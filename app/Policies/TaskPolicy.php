<?php

namespace App\Policies;

use App\Models\Task;
use App\Models\User;

class TaskPolicy
{
    /**
     * Peuvent voir une tâche : l'employé à qui elle est assignée, son créateur,
     * ou un admin/RH du même tenant.
     */
    public function view(User $user, Task $task): bool
    {
        if ($task->assigned_to === $user->id) {
            return true;
        }

        if ($task->user_id === $user->id) {
            return true;
        }

        return $user->isAdminOrRh() && $task->tenant_id === $user->tenant_id;
    }

    /**
     * Modifier une tâche = changer son statut, y logger du temps (activités
     * manuelles ou chrono). Autorisé pour l'employé assigné (c'est son travail
     * au quotidien), le créateur de la tâche, ou un admin/RH du même tenant.
     */
    public function update(User $user, Task $task): bool
    {
        return $task->assigned_to === $user->id
            || $task->user_id === $user->id
            || ($user->isAdminOrRh() && $task->tenant_id === $user->tenant_id);
    }

    /**
     * Supprimer une tâche reste réservé à son créateur ou à un admin/RH —
     * un simple assigné ne doit pas pouvoir effacer une tâche qu'on lui a confiée.
     */
    public function delete(User $user, Task $task): bool
    {
        return $task->user_id === $user->id
            || ($user->isAdminOrRh() && $task->tenant_id === $user->tenant_id);
    }
}
