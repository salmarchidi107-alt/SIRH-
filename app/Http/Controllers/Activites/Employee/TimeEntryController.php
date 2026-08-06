<?php

namespace App\Http\Controllers\Activites\Employee;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTimeEntryRequest;
use App\Models\Activity;
use App\Models\Project;
use App\Models\Task;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class TimeEntryController extends Controller
{
    /** Historique des saisies de l'employé + formulaire de saisie. */
    public function index(): View
    {
        $user = Auth::user();

        // Uniquement les projets où l'employé a au moins une tâche assignée.
        $projects = Project::query()
            ->tenant($user->tenant_id)
            ->whereHas('tasks', fn ($q) => $q->where('assigned_to', $user->id))
            ->orderBy('name')
            ->get(['id', 'name']);

        $entries = Activity::query()
            ->tenant($user->tenant_id)
            ->where('user_id', $user->id)
            ->with(['task.project'])
            ->orderByDesc('activity_date')
            ->orderByDesc('id')
            ->paginate(20);

        return view('activites.employee.time-entries', compact('projects', 'entries'));
    }

    /** Retourne (en JSON) les tâches assignées à l'employé pour un projet donné — utilisé par le select dynamique du formulaire. */
    public function tasksForProject(Project $project): JsonResponse
    {
        $user = Auth::user();
        abort_unless($project->tenant_id === $user->tenant_id, 403);

        $tasks = Task::query()
            ->where('project_id', $project->id)
            ->where('assigned_to', $user->id)
            ->orderBy('title')
            ->get(['id', 'title', 'estimated_minutes']);

        return response()->json($tasks->map(fn (Task $task) => [
            'id' => $task->id,
            'title' => $task->title,
            'estimated_minutes' => $task->estimated_minutes,
            'logged_minutes' => $task->logged_minutes,
            'estimated_human' => $task->estimated_minutes ? \App\Support\Duration::toHuman($task->estimated_minutes) : null,
            'logged_human' => \App\Support\Duration::toHuman($task->logged_minutes),
        ]));
    }

    public function store(StoreTimeEntryRequest $request): RedirectResponse
    {
        $user = Auth::user();

        $task = Task::where('id', $request->validated()['task_id'])
            ->where('assigned_to', $user->id)
            ->where('project_id', $request->validated()['project_id'])
            ->firstOrFail();

        Activity::create($request->validatedForCreate($task, $user->id));

        return back()->with('success', 'Saisie de temps enregistrée.');
    }

    public function destroy(Activity $activity): RedirectResponse
    {
        $user = Auth::user();
        abort_unless($activity->user_id === $user->id, 403);
        // On ne permet la suppression que si la saisie n'a pas déjà été validée par l'admin.
        abort_if($activity->status === 'validee', 403, 'Cette saisie a déjà été validée et ne peut plus être supprimée.');

        $activity->delete();

        return back()->with('success', 'Saisie supprimée.');
    }
}