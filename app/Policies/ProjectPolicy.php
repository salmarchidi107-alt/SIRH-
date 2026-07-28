<?php

namespace App\Policies;

use App\Models\Project;
use App\Models\Task;
use App\Models\User;

class ProjectPolicy
{
    /**
     * Peuvent voir un projet : son créateur, un admin/RH du même tenant,
     * ou un employé qui a au moins une tâche assignée dans ce projet
     * (même s'il n'en est pas le propriétaire).
     */
    public function view(User $user, Project $project): bool
    {
        if ($project->user_id === $user->id) {
            return true;
        }

        if ($user->isAdminOrRh() && $project->tenant_id === $user->tenant_id) {
            return true;
        }

        return Task::where('project_id', $project->id)
            ->where('assigned_to', $user->id)
            ->exists();
    }

    /** Gérer un projet (le modifier, y créer des tâches) : propriétaire ou admin/RH du tenant. */
    public function update(User $user, Project $project): bool
    {
        return $project->user_id === $user->id
            || ($user->isAdminOrRh() && $project->tenant_id === $user->tenant_id);
    }

    /** Créer une tâche dans ce projet : même règle que "gérer le projet". */
    public function createTask(User $user, Project $project): bool
    {
        return $this->update($user, $project);
    }

    public function delete(User $user, Project $project): bool
    {
        return $this->update($user, $project);
    }
}
