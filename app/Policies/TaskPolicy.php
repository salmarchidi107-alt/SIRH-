<?php

namespace App\Policies;

use App\Models\Task;
use App\Models\User;

class TaskPolicy
{
    /**
     * Le propriétaire voit sa tâche.
     * Admin/RH voient les tâches du même tenant (RH : si le module leur est activé).
     */
    public function view(User $user, Task $task): bool
    {
        if ($task->user_id === $user->id) {
            return true;
        }

        return $user->isAdminOrRh()
            && $task->tenant_id === $user->tenant_id
            && $user->canView('activites');
    }

    /** Seul le propriétaire peut modifier sa tâche / y ajouter des activités / piloter le chrono. */
    public function update(User $user, Task $task): bool
    {
        return $task->user_id === $user->id;
    }

    public function delete(User $user, Task $task): bool
    {
        return $task->user_id === $user->id
            || ($user->isAdminOrRh() && $task->tenant_id === $user->tenant_id);
    }
}
s
