<?php

namespace App\Http\Controllers\Activites\Admin;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{

    public function index(Request $request): View
    {
        $user = Auth::user();
        abort_unless($user->isAdminOrRh(), 403);

        $tenantId = $user->tenant_id;

        $totalTasks = Task::tenant($tenantId)->count();
        $doneTasks = Task::tenant($tenantId)->where('status', 'terminee')->count();
        $lateTasks = Task::tenant($tenantId)
            ->whereNotIn('status', ['terminee', 'annulee'])
            ->whereDate('due_date', '<', now()->toDateString())
            ->count();
        // "En cours" au sens large : tout ce qui n'est ni terminé, ni annulé, ni en retard.
        $inProgressTasks = $totalTasks - $doneTasks - $lateTasks
            - Task::tenant($tenantId)->where('status', 'annulee')->count();

        $stats = [
            'total_projects' => Project::tenant($tenantId)->count(),
            'total_tasks' => $totalTasks,
            'done_tasks' => $doneTasks,
            'in_progress_tasks' => max(0, $inProgressTasks),
            'late_tasks' => $lateTasks,
            'completion_rate' => $totalTasks > 0 ? (int) round($doneTasks / $totalTasks * 100) : 0,
        ];

        $employees = User::where('tenant_id', $tenantId)->orderBy('name')->get(['id', 'name']);
        $employeeProgress = $this->buildEmployeeProgress($tenantId, $employees);

        $projects = Project::tenant($tenantId)->orderBy('name')->get(['id', 'name', 'status']);
        $projectProgress = $this->buildProjectProgress($tenantId, $projects);

        $globalStats = [
            'employees_with_tasks' => count($employeeProgress),
            'employees_fully_done' => collect($employeeProgress)->where('complete', true)->count(),
        ];

        return view('activites.admin.dashboard', compact('stats', 'employeeProgress', 'projectProgress', 'globalStats'));
    }

    private function buildProjectProgress(?string $tenantId, \Illuminate\Support\Collection $projects): array
    {
        $taskCounts = Task::tenant($tenantId)
            ->select('project_id', 'status', DB::raw('count(*) as total'))
            ->groupBy('project_id', 'status')
            ->get()
            ->groupBy('project_id');

        $lateCounts = Task::tenant($tenantId)
            ->whereNotIn('status', ['terminee', 'annulee'])
            ->whereDate('due_date', '<', now()->toDateString())
            ->select('project_id', DB::raw('count(*) as total'))
            ->groupBy('project_id')
            ->pluck('total', 'project_id');

        return $projects->map(function (Project $project) use ($taskCounts, $lateCounts) {
            $rows = $taskCounts->get($project->id, collect());
            $total = (int) $rows->sum('total');
            $annulees = (int) ($rows->firstWhere('status', 'annulee')->total ?? 0);
            $done = (int) ($rows->firstWhere('status', 'terminee')->total ?? 0);
            $late = (int) ($lateCounts[$project->id] ?? 0);
            $remaining = $total - $done - $annulees;

            return [
                'id' => $project->id,
                'name' => $project->name,
                'status' => $project->status,
                'total' => $total,
                'done' => $done,
                'remaining' => max(0, $remaining),
                'late' => $late,
                'percent' => $total > 0 ? (int) round($done / $total * 100) : 0,
                'complete' => $total > 0 && $remaining <= 0,
            ];
        })->filter(fn (array $row) => $row['total'] > 0)->values()->all();
    }

    private function buildEmployeeProgress(?string $tenantId, \Illuminate\Support\Collection $employees): array
    {
        $taskCounts = Task::tenant($tenantId)
            ->select('assigned_to', 'status', DB::raw('count(*) as total'))
            ->whereNotNull('assigned_to')
            ->groupBy('assigned_to', 'status')
            ->get()
            ->groupBy('assigned_to');

        $lateCounts = Task::tenant($tenantId)
            ->whereNotIn('status', ['terminee', 'annulee'])
            ->whereDate('due_date', '<', now()->toDateString())
            ->select('assigned_to', DB::raw('count(*) as total'))
            ->whereNotNull('assigned_to')
            ->groupBy('assigned_to')
            ->pluck('total', 'assigned_to');

        return $employees->map(function (User $employee) use ($taskCounts, $lateCounts) {
            $rows = $taskCounts->get($employee->id, collect());
            $total = (int) $rows->sum('total');
            $annulees = (int) ($rows->firstWhere('status', 'annulee')->total ?? 0);
            $done = (int) ($rows->firstWhere('status', 'terminee')->total ?? 0);
            $late = (int) ($lateCounts[$employee->id] ?? 0);
            $remaining = $total - $done - $annulees;

            return [
                'id' => $employee->id,
                'name' => $employee->name,
                'total' => $total,
                'done' => $done,
                'remaining' => max(0, $remaining),
                'late' => $late,
                'percent' => $total > 0 ? (int) round($done / $total * 100) : 0,
                'complete' => $total > 0 && $remaining <= 0,
            ];
        })->filter(fn (array $row) => $row['total'] > 0)->values()->all();
    }
}
