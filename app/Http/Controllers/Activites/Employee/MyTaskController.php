<?php

namespace App\Http\Controllers\Activites\Employee;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\Task;
use App\Support\Duration;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class MyTaskController extends Controller
{
    /** Toutes les tâches assignées à l'employé connecté, tous projets confondus. */
    public function index(Request $request): View
    {
        $user = Auth::user();

        $query = Task::query()
            ->tenant($user->tenant_id)
            ->assignedTo($user->id)
            ->with('project');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $tasks = $query
            ->orderByRaw("FIELD(status, 'en_cours','en_pause','a_faire','terminee','annulee')")
            ->orderByDesc('updated_at')
            ->get();

        $stats = [
            'remaining' => $tasks->whereNotIn('status', ['terminee', 'annulee'])->count(),
            'late' => $tasks->filter(fn (Task $t) => $t->isLate())->count(),
        ];

        // Projets disponibles pour la création d'une tâche par l'employé
        // (il ne crée pas de projet, il choisit parmi les projets actifs de l'entreprise).
        $projects = Project::tenant($user->tenant_id)->where('status', 'actif')->orderBy('name')->get(['id', 'name']);

        return view('activites.employee.my-tasks', compact('tasks', 'stats', 'projects'));
    }

    /**
     * L'employé crée sa propre tâche sur un projet existant de son choix et
     * se l'assigne automatiquement. La durée estimée est obligatoire ici
     * (contrairement à la création admin) car elle sert ensuite de référence
     * dans le suivi des activités et la gestion de son temps.
     */
    public function store(Request $request): RedirectResponse
    {
        $user = Auth::user();

        $data = $request->validate([
            'project_id' => 'required|integer',
            'title' => 'required|string|max:200',
            'description' => 'nullable|string|max:5000',
            'priority' => 'nullable|in:' . implode(',', Task::PRIORITIES),
            'due_date' => 'nullable|date',
            'estimated_duration' => 'required|string|max:20',
        ]);

        $project = Project::where('id', $data['project_id'])
            ->where('tenant_id', $user->tenant_id)
            ->where('status', 'actif')
            ->firstOrFail();

        $estimatedMinutes = Duration::toMinutes($data['estimated_duration']);
        if ($estimatedMinutes === null) {
            return back()->withErrors(['estimated_duration' => "Format invalide. Exemples valides : 4h, 1h30, 45m."])->withInput();
        }

        $task = Task::create([
            'project_id' => $project->id,
            'user_id' => $user->id,
            'assigned_to' => $user->id,
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'priority' => $data['priority'] ?? 'normale',
            'status' => 'a_faire',
            'due_date' => $data['due_date'] ?? null,
            'estimated_minutes' => $estimatedMinutes,
        ]);

        return back()->with('success', "Tâche « {$task->title} » créée.");
    }

    /**
     * Mise à jour restreinte : seuls statut, % d'avancement et commentaire
     * sont modifiables par l'employé. Le reste (projet, titre, description,
     * priorité, dates, estimation, assignation) reste réservé à l'admin.
     */
    public function update(Request $request, Task $task): RedirectResponse
    {
        $user = Auth::user();
        abort_unless($task->tenant_id === $user->tenant_id && $task->assigned_to === $user->id, 403);

        $data = $request->validate([
            'status' => 'required|in:' . implode(',', Task::STATUSES),
            'percent_complete' => 'required|integer|min:0|max:100',
            'employee_comment' => 'nullable|string|max:2000',
        ]);

        $task->applyEmployeeUpdate($data);

        return back()->with('success', 'Tâche mise à jour.');
    }
}
