<?php

namespace App\Http\Controllers;

use App\Http\Requests\Employee\StoreEmployeeRequest;
use App\Http\Requests\Employee\UpdateEmployeeRequest;
use App\Models\Employee;
use App\Services\Employee\EmployeeQueryService;
use Illuminate\Http\Request;

class EmployeeController extends Controller
{
    public function __construct(private EmployeeQueryService $queryService) {}

    public function index(Request $request)
    {
        $filter = $request->get('filter', 'all');
        $data = $this->queryService->list($request);
        $data['filter'] = $filter;
        $data['departments'] = Employee::where('tenant_id', config('app.current_tenant_id'))
            ->whereNotNull('department')
            ->distinct('department')
            ->pluck('department')
            ->filter()
            ->sort()
            ->values();

        return view('employees.index', $data);
    }

    public function create()
    {
        $managers = $this->queryService->getManagersForEmployee();
        $users    = $this->queryService->getAvailableUsers();

        return view('employees.create', compact('managers', 'users'));
    }

    public function store(StoreEmployeeRequest $request)
    {
        app(\App\Services\Employee\EmployeeCreator::class)->create($request->validated(), $request);

        return redirect()->route('employees.index')
            ->with('success', 'Employé créé avec succès.');
    }

    public function show(Employee $employee)
    {
        $user = auth()->user();

        // Un non-admin ne peut voir que sa propre fiche
        if (! $user->isAdmin()) {
            $linked = Employee::where('user_id', $user->id)->first();

            if (! $linked || $linked->id !== $employee->id) {
                abort(403, 'Accès réservé aux administrateurs du tenant.');
            }
        }

        $employee->load([
            'absences' => fn ($q) => $q->latest()->take(10),
            'salaries'  => fn ($q) => $q->latest()->take(6),
        ]);

        return view('employees.show', compact('employee'));
    }

    public function edit(Employee $employee)
    {
        $managers      = $this->queryService->getManagersForEmployee($employee->id);
        $linkedUserIds = Employee::whereNotNull('user_id')
            ->where('id', '!=', $employee->id)
            ->pluck('user_id');
        $users = \App\Models\User::whereNotIn('id', $linkedUserIds)->select('id')->get();

        return view('employees.edit', compact('employee', 'managers', 'users'));
    }

    public function update(UpdateEmployeeRequest $request, Employee $employee)
    {
        $validated = $request->validated();

        if ($request->hasFile('photo')) {
            $validated['photo'] = $request->file('photo')->store('photos', 'public');
        }

        $employee->update($validated);

        return redirect()->route('employees.show', $employee)
            ->with('success', 'Employé mis à jour avec succès.');
    }

    public function destroy(Employee $employee)
    {
        $employee->delete();

        return redirect()->route('employees.index')
            ->with('success', 'Employé supprimé.');
    }
}
