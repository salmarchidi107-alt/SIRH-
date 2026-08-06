<?php

namespace App\Http\Controllers\Activites\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAdminTaskRequest;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use App\Support\Duration;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class TaskController extends Controller
{
    public function index(Request $request): View
    {
        $user = Auth::user();
        abort_unless($user->isAdmin(), 403);

        $tasks = $this->filteredQuery($request, $user->tenant_id)
            ->with(['owner', 'assignee', 'project'])
            ->orderByDesc('updated_at')
            ->paginate(20)
            ->withQueryString();

        $employees = User::where('tenant_id', $user->tenant_id)
    ->whereHas('employee', fn ($q) => $q->active())
    ->orderBy('name')
    ->get(['id', 'name']);
        $projects = Project::query()->tenant($user->tenant_id)->orderBy('name')->get(['id', 'name']);
        // Seuls les projets actifs sont proposés pour créer une NOUVELLE tâche
        // (un projet archivé n'est plus une cible valide pour du nouveau travail).
        $activeProjects = $projects->where('status', 'actif')->values();

        return view('activites.admin.tasks.index', compact('tasks', 'employees', 'projects', 'activeProjects'));
    }

    public function store(StoreAdminTaskRequest $request): RedirectResponse
    {
        $user = Auth::user();

        $this->assertBelongsToTenant($request->input('project_id'), $request->input('assigned_to'), $user->tenant_id);

        $task = Task::create($request->validatedForCreate($user->id));

        return redirect()
            ->route('activites.admin.tasks.show', $task)
            ->with('success', "Tâche « {$task->title} » créée et assignée à {$task->assignee->name}.");
    }

    public function show(Request $request, Task $task): View
    {
        $user = Auth::user();
        abort_unless($user->isAdmin() && $task->tenant_id === $user->tenant_id, 403);

        $task->load(['project', 'owner', 'assignee', 'activities.user']);

        $employees = User::where('tenant_id', $user->tenant_id)
    ->whereHas('employee', fn ($q) => $q->active())
    ->orderBy('name')
    ->get(['id', 'name']);
        $projects = Project::query()->tenant($user->tenant_id)->orderBy('name')->get(['id', 'name']);

        return view('activites.admin.tasks.show', compact('task', 'employees', 'projects'));
    }

    public function update(StoreAdminTaskRequest $request, Task $task): RedirectResponse
    {
        $user = Auth::user();
        abort_unless($user->isAdmin() && $task->tenant_id === $user->tenant_id, 403);

        $this->assertBelongsToTenant($request->input('project_id'), $request->input('assigned_to'), $user->tenant_id);

        $task->update($request->validatedForUpdate());

        return redirect()
            ->route('activites.admin.tasks.show', $task)
            ->with('success', 'Tâche mise à jour.');
    }

    public function destroy(Request $request, Task $task): RedirectResponse
    {
        $user = Auth::user();
        abort_unless($user->isAdmin() && $task->tenant_id === $user->tenant_id, 403);

        $task->delete();

        return redirect()->route('activites.admin.tasks.index')->with('success', 'Tâche supprimée.');
    }

    public function exportExcel(Request $request)
    {
        $user = Auth::user();
        abort_unless($user->isAdmin(), 403);

        $tasks = $this->filteredQuery($request, $user->tenant_id)->with(['owner', 'assignee', 'project'])->get();

        $filename = 'taches-' . now()->format('Y-m-d') . '.csv';

        $callback = function () use ($tasks) {
            $handle = fopen('php://output', 'w');
            fwrite($handle, "\xEF\xBB\xBF");

            fputcsv($handle, [
                'Projet', 'Tâche', 'Assignée à', 'Créée par', 'Priorité', 'Statut',
                'Début', 'Échéance', '% avancement', 'Temps loggé', 'Temps estimé',
            ], ';');

            foreach ($tasks as $task) {
                fputcsv($handle, [
                    $task->project->name ?? '',
                    $task->title,
                    $task->assignee->name ?? '',
                    $task->owner->name ?? '',
                    $task->priorityLabel(),
                    $task->statusLabel(),
                    optional($task->start_date)->format('d/m/Y'),
                    optional($task->due_date)->format('d/m/Y'),
                    $task->percent_complete . '%',
                    Duration::toHuman($task->logged_minutes),
                    $task->estimated_minutes ? Duration::toHuman($task->estimated_minutes) : '',
                ], ';');
            }

            fclose($handle);
        };

        return response()->streamDownload($callback, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function exportPdf(Request $request)
    {
        $user = Auth::user();
        abort_unless($user->isAdmin(), 403);

        $tasks = $this->filteredQuery($request, $user->tenant_id)->with(['owner', 'assignee', 'project'])->get();
        $tenant = $user->tenant;

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('activites.admin.tasks.export-pdf', compact('tasks', 'tenant'))
            ->setPaper('a4', 'landscape');

        return $pdf->download('taches-' . now()->format('Y-m-d') . '.pdf');
    }

    private function filteredQuery(Request $request, ?string $tenantId)
    {
        $query = Task::query()->tenant($tenantId);

        if ($request->filled('q')) {
            $query->where('title', 'like', '%' . $request->q . '%');
        }
        if ($request->filled('user_id')) {
            $query->where('assigned_to', $request->user_id);
        }
        if ($request->filled('project_id')) {
            $query->where('project_id', $request->project_id);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }
        if ($request->filled('date_from')) {
            $query->whereDate('due_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('due_date', '<=', $request->date_to);
        }

        return $query;
    }

    private function assertBelongsToTenant(?int $projectId, ?int $assignedTo, ?string $tenantId): void
    {
        $projectOk = Project::where('id', $projectId)->where('tenant_id', $tenantId)->exists();
        abort_unless($projectOk, 422, 'Projet invalide.');

        $employeeOk = User::where('id', $assignedTo)->where('tenant_id', $tenantId)->exists();
        abort_unless($employeeOk, 422, 'Employé invalide.');
    }
}