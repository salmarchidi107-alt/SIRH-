<?php

namespace App\Http\Controllers;

use App\Models\Room;
use App\Models\Department;
use App\Models\Employee;
use Illuminate\Http\Request;

class ParametrageController extends Controller
{
    private function ensureTenant(): void
    {
        if (blank(config('app.current_tenant_id')) && auth()->check()) {
            $tenantId = auth()->user()->tenant_id;
            if ($tenantId) {
                config(['app.current_tenant_id' => $tenantId]);
            }
        }
    }

    public function index(Request $request)
    {
        $this->ensureTenant();

        $tenantId    = auth()->user()->tenant_id;
        $rooms       = Room::with('department')->orderBy('name')->get();
        $departments = Department::withCount('rooms')->orderBy('name')->get();

        // ── Documents employés ────────────────────────────────────────────
        $employeesQuery = Employee::where('tenant_id', $tenantId)
            ->select([
                'id', 'first_name', 'last_name', 'department',
                'doc_casier_path', 'doc_rib_path', 'doc_diplomes_path',
                'doc_cin_path', 'doc_contrat_path',
            ])
            ->orderBy('last_name');

        // Filtre par département
        if ($request->filled('dept_filter')) {
            $employeesQuery->where('department', $request->dept_filter);
        }

        $employeesWithDocs = $employeesQuery->get();

        // Liste unique des départements pour le filtre
        $departmentNames = Employee::where('tenant_id', $tenantId)
            ->whereNotNull('department')
            ->distinct()
            ->orderBy('department')
            ->pluck('department');

        return view('parametrage.index', compact(
            'rooms', 'departments', 'employeesWithDocs', 'departmentNames'
        ));
    }
}
