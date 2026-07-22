<?php

namespace App\Http\Controllers\Activites;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreActivityRequest;
use App\Models\Activity;
use App\Models\Project;
use App\Models\Task;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class ActivityController extends Controller
{
    /** Ajoute une saisie de temps manuelle sur une tâche précise (pas de sélection de tâche : on est déjà dessus). */
    public function store(StoreActivityRequest $request, Project $project, Task $task): RedirectResponse
    {
        abort_unless($task->project_id === $project->id, 404);
        $this->authorize('update', $task);

        $path = null;
        if ($request->hasFile('attachment')) {
            $path = $request->file('attachment')->store('activites/' . $task->id, 'public');
        }

        $task->activities()->create([
            'tenant_id' => $task->tenant_id,
            'user_id' => Auth::id(),
            'type' => 'manuelle',
            'activity_date' => $request->validated()['activity_date'],
            'duration_minutes' => $request->durationInMinutes(),
            'comment' => $request->validated()['comment'] ?? null,
            'attachment_path' => $path,
        ]);

        return back()->with('success', 'Activité enregistrée.');
    }

    public function destroy(Project $project, Task $task, Activity $activity): RedirectResponse
    {
        abort_unless($task->project_id === $project->id, 404);
        $this->authorize('update', $task);
        abort_unless($activity->task_id === $task->id, 404);

        $activity->delete();

        return back()->with('success', 'Activité supprimée.');
    }
}
