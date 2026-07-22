<?php

namespace App\Http\Controllers\Activites;

use App\Http\Controllers\Controller;
use App\Models\Activity;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AdminController extends Controller
{
    public function index(Request $request): View
    {
        $user = Auth::user();
        abort_unless($user->isAdminOrRh() && $user->canView('activites'), 403);

        $tenantId = $user->tenant_id;

        $query = Task::query()->tenant($tenantId)->with(['owner', 'project'])->withCount('activities');

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
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

        $tasks = $query->orderByDesc('updated_at')->paginate(20)->withQueryString();

        $stats = [
            'active_tasks' => Task::tenant($tenantId)->whereNotIn('status', ['terminee', 'annulee'])->count(),
            'today_minutes' => Activity::tenant($tenantId)->whereDate('activity_date', now()->toDateString())->sum('duration_minutes'),
            'late_tasks' => Task::tenant($tenantId)
                ->whereNotIn('status', ['terminee', 'annulee'])
                ->whereDate('due_date', '<', now()->toDateString())
                ->count(),
            'active_employees' => Task::tenant($tenantId)->whereNotNull('timer_started_at')->distinct('user_id')->count('user_id'),
            'total_employees' => User::where('tenant_id', $tenantId)->count(),
        ];

        $employees = User::where('tenant_id', $tenantId)->orderBy('name')->get(['id', 'name']);
        $projects = Project::tenant($tenantId)->orderBy('name')->get(['id', 'name']);

        return view('activites.admin', compact('tasks', 'stats', 'employees', 'projects'));
    }
}
