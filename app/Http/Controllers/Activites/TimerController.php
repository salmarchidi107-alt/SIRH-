<?php

namespace App\Http\Controllers\Activites;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\Task;
use Illuminate\Http\RedirectResponse;

class TimerController extends Controller
{
    public function start(Project $project, Task $task): RedirectResponse
    {
        abort_unless($task->project_id === $project->id, 404);
        $this->authorize('update', $task);

        $task->startTimer();

        return back()->with('success', 'Chronomètre démarré.');
    }

    public function pause(Project $project, Task $task): RedirectResponse
    {
        abort_unless($task->project_id === $project->id, 404);
        $this->authorize('update', $task);

        $task->stopTimer('en_pause');

        return back()->with('success', 'Tâche mise en pause, temps enregistré.');
    }

    public function finish(Project $project, Task $task): RedirectResponse
    {
        abort_unless($task->project_id === $project->id, 404);
        $this->authorize('update', $task);

        $task->stopTimer('terminee');

        return back()->with('success', 'Tâche terminée, temps enregistré.');
    }
}
