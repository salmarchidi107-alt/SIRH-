<?php

namespace App\Http\Controllers\Activites\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAdminProjectRequest;
use App\Models\Project;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ProjectController extends Controller
{
    public function index(Request $request): View
    {
        $user = Auth::user();
        abort_unless($user->isAdminOrRh(), 403);

        $projects = $this->filteredQuery($request, $user->tenant_id)
            ->orderByDesc('updated_at')
            ->paginate(20)
            ->withQueryString();

        $employees = User::where('tenant_id', $user->tenant_id)->orderBy('name')->get(['id', 'name']);

        return view('activites.admin.projects.index', compact('projects', 'employees'));
    }

    public function store(StoreAdminProjectRequest $request): RedirectResponse
    {
        $project = Project::create($request->validatedForCreate(Auth::id()));

        return redirect()
            ->route('activites.admin.projects.show', $project)
            ->with('success', "Projet « {$project->name} » créé.");
    }

    public function show(Request $request, Project $project): View
    {
        $user = Auth::user();
        abort_unless($user->isAdminOrRh() && $project->tenant_id === $user->tenant_id, 403);

        $project->load(['tasks.assignee', 'tasks.owner']);

        return view('activites.admin.projects.show', compact('project'));
    }

    public function update(StoreAdminProjectRequest $request, Project $project): RedirectResponse
    {
        $user = Auth::user();
        abort_unless($user->isAdminOrRh() && $project->tenant_id === $user->tenant_id, 403);

        $project->update($request->validatedForUpdate());

        return redirect()
            ->route('activites.admin.projects.show', $project)
            ->with('success', 'Projet mis à jour.');
    }

    public function destroy(Request $request, Project $project): RedirectResponse
    {
        $user = Auth::user();
        abort_unless($user->isAdminOrRh() && $project->tenant_id === $user->tenant_id, 403);

        $project->delete();

        return redirect()->route('activites.admin.projects.index')->with('success', 'Projet supprimé.');
    }

    public function exportExcel(Request $request)
    {
        $user = Auth::user();
        abort_unless($user->isAdminOrRh(), 403);

        $projects = $this->filteredQuery($request, $user->tenant_id)
            ->withCount(['tasks', 'tasks as done_tasks_count' => fn ($q) => $q->where('status', 'terminee')])
            ->orderBy('name')
            ->get();

        $filename = 'projets-' . now()->format('Y-m-d') . '.csv';

        $callback = function () use ($projects) {
            $handle = fopen('php://output', 'w');
            fwrite($handle, "\xEF\xBB\xBF");

            fputcsv($handle, ['Projet', 'Description', 'Statut', 'Tâches', 'Terminées', 'Créé le'], ';');

            foreach ($projects as $project) {
                fputcsv($handle, [
                    $project->name,
                    $project->description,
                    $project->statusLabel(),
                    $project->tasks_count,
                    $project->done_tasks_count,
                    $project->created_at->format('d/m/Y'),
                ], ';');
            }

            fclose($handle);
        };

        return response()->streamDownload($callback, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function exportPdf(Request $request)
    {
        $user = Auth::user();
        abort_unless($user->isAdminOrRh(), 403);

        $projects = $this->filteredQuery($request, $user->tenant_id)
            ->withCount(['tasks', 'tasks as done_tasks_count' => fn ($q) => $q->where('status', 'terminee')])
            ->orderBy('name')
            ->get();
        $tenant = $user->tenant;


        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('activites.admin.projects.export-pdf', compact('projects', 'tenant'));

        return $pdf->download('projets-' . now()->format('Y-m-d') . '.pdf');
    }

    private function filteredQuery(Request $request, ?string $tenantId)
    {
        $query = Project::query()->tenant($tenantId);

        if ($request->filled('q')) {
            $query->where('name', 'like', '%' . $request->q . '%');
        }
        if ($request->filled('description')) {
            $query->where('description', 'like', '%' . $request->description . '%');
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        return $query;
    }
}
