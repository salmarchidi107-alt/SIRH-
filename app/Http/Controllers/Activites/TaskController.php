<?php

namespace App\Http\Controllers\Activites;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTaskRequest;
use App\Models\Project;
use App\Models\Task;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class TaskController extends Controller
{
    /** Crée une tâche à l'intérieur d'un projet existant. */
    public function store(StoreTaskRequest $request, Project $project): RedirectResponse
    {
        $this->authorize('update', $project); // seul le propriétaire du projet y ajoute des tâches

        $task = Task::create($request->validatedForCreate($project->id, Auth::id()));

        return redirect()
            ->route('activites.tasks.show', [$project, $task])
            ->with('success', 'Tâche créée avec succès.');
    }

    public function show(Request $request, Project $project, Task $task): View
    {
        abort_unless($task->project_id === $project->id, 404);
        $this->authorize('view', $task);

        $task->load('activities');

        return view('activites.tasks.show', compact('project', 'task'));
    }

    public function updateStatus(Request $request, Project $project, Task $task): RedirectResponse
    {
        abort_unless($task->project_id === $project->id, 404);
        $this->authorize('update', $task);

        $request->validate([
            'status' => 'required|in:' . implode(',', Task::STATUSES),
        ]);

        if ($task->is_timer_running) {
            $task->stopTimer($request->status);
        } else {
            $task->update([
                'status' => $request->status,
                'completed_at' => $request->status === 'terminee' ? now() : $task->completed_at,
            ]);
        }

        return back()->with('success', 'Statut mis à jour.');
    }

    public function destroy(Request $request, Project $project, Task $task): RedirectResponse
    {
        abort_unless($task->project_id === $project->id, 404);
        $this->authorize('delete', $task);

        $task->delete();

        return redirect()->route('activites.projects.show', $project)->with('success', 'Tâche supprimée.');
    }
}
