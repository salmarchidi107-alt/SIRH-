<?php

namespace App\Services\Employee;

use App\Models\Employee;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EmployeeQueryService
{
    /**
     * Get paginated list of employees with search/filter.
     */
    public function list(Request $request): array
    {
        $query = Employee::with(['department', 'user'])
            ->where('tenant_id', config('app.current_tenant_id'))
            ->when($request->search, function ($q) use ($request) {
                $q->where('matricule', 'like', "%{$request->search}%")
                  ->orWhere('last_name', 'like', "%{$request->search}%")
                  ->orWhere('first_name', 'like', "%{$request->search}%");
            })
            ->when($request->department_id, fn($q, $v) => $q->where('department_id', $v))
            ->when($request->manager, fn($q) => $q->where('is_manager', true));

        $employees = $query->paginate(15)->appends($request->query());

        return [
            'employees' => $employees,
            'filters' => $request->only(['search', 'department_id', 'manager'])
        ];
    }

    /**
     * Get managers for employee dropdown (exclude specific ID).
     */
    public function getManagersForEmployee(?int $excludeId = null): \Illuminate\Database\Eloquent\Collection
    {
        $query = Employee::where('is_manager', true)
            ->where('tenant_id', config('app.current_tenant_id'))
            ->when($excludeId, fn($q, $id) => $q->where('id', '!=', $id))
            ->orderBy('last_name')
            ->get(['id', 'last_name', 'first_name']);

        return $query;
    }

    /**
     * Get available users not linked to any employee.
     */
    public function getAvailableUsers(): \Illuminate\Database\Eloquent\Collection
    {
        $linkedUserIds = Employee::whereNotNull('user_id')
            ->where('tenant_id', config('app.current_tenant_id'))
            ->pluck('user_id');

        return User::where('tenant_id', config('app.current_tenant_id'))
            ->whereNotIn('id', $linkedUserIds)
            ->get(['id', 'name']);
    }
}
