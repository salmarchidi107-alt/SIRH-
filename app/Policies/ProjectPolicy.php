<?php

namespace App\Policies;

use App\Models\Project;
use App\Models\User;

class ProjectPolicy
{
    public function view(User $user, Project $project): bool
    {
        if ($project->user_id === $user->id) {
            return true;
        }

        return $user->isAdminOrRh()
            && $project->tenant_id === $user->tenant_id
            && $user->canView('activites');
    }

    public function update(User $user, Project $project): bool
    {
        return $project->user_id === $user->id;
    }

    public function delete(User $user, Project $project): bool
    {
        return $project->user_id === $user->id
            || ($user->isAdminOrRh() && $project->tenant_id === $user->tenant_id);
    }
}
