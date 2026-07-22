<?php

namespace App\Http\Controllers\Activites;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProjectRequest;
use App\Models\Activity;
use App\Models\Project;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ProjectController extends Controller
{
    /** Liste des projets de l'employé connecté + formulaire de création. */
    public function index(Request $request): View
    {
        $user = Auth::user();

        $projects = Project::query()
            ->tenant($user->tenant_id)
            ->where('user_id', $user->id)
            ->withCount('tasks')
            ->orderByDesc('updated_at')
            ->get();

        $stats = [
            'today_minutes' => Activity::tenant($user->tenant_id)
                ->where('user_id', $user->id)
                ->whereDate('activity_date', now()->toDateString())
                ->sum('duration_minutes'),
            'week_minutes' => Activity::tenant($user->tenant_id)
                ->where('user_id', $user->id)
                ->whereBetween('activity_date', [now()->startOfWeek(), now()->endOfWeek()])
                ->sum('duration_minutes'),
            'active_projects' => $projects->where('status', 'actif')->count(),
        ];

        return view('activites.projects.index', compact('projects', 'stats'));
    }

    public function store(StoreProjectRequest $request): RedirectResponse
    {
        $project = Project::create($request->validatedForCreate(Auth::id()));

        return redirect()
            ->route('activites.projects.show', $project)
            ->with('success', 'Projet créé avec succès.');
    }

    public function show(Request $request, Project $project): View
    {
        $this->authorize('view', $project);

        $project->load(['tasks.activities']);

        return view('activites.projects.show', compact('project'));
    }

    public function destroy(Request $request, Project $project): RedirectResponse
    {
        $this->authorize('delete', $project);

        $project->delete();

        return redirect()->route('activites.projects.index')->with('success', 'Projet supprimé.');
    }
}
