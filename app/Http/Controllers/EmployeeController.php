<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\User;
use Illuminate\Http\Request;

class EmployeeController extends Controller
{
    public function index(Request $request)
    {
        $query = Employee::query();

        // Filter by status: 'all' or 'active'
        $filter = $request->get('filter', 'all');
        if ($filter === 'active') {
            $query->where('status', 'active');
        }

        if ($request->search) {
            $query->where(function($q) use ($request) {
                $q->where('first_name', 'like', "%{$request->search}%")
                  ->orWhere('last_name', 'like', "%{$request->search}%")
                  ->orWhere('matricule', 'like', "%{$request->search}%")
                  ->orWhere('email', 'like', "%{$request->search}%");
            });
        }

        if ($request->department) {
            $query->where('department', $request->department);
        }

        if ($request->status) {
            $query->where('status', $request->status);
        }

        $employees = $query->latest()->paginate(15);
        $departments = Employee::distinct()->pluck('department');
        $filter = $request->get('filter', 'all');

        return view('employees.index', compact('employees', 'departments', 'filter'));
    }

    public function create()
    {
        $managers = Employee::where('status', 'active')->get();
        // Get users that are not already linked to employees
        $linkedUserIds = Employee::whereNotNull('user_id')->pluck('user_id');
        $users = User::whereNotIn('id', $linkedUserIds)->get();
        return view('employees.create', compact('managers', 'users'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'email' => 'required|email|unique:employees',
            'phone' => 'nullable|string|max:20',
            'department' => 'required|string|max:100',
            'position' => 'required|string|max:100',
            'diploma_type' => 'nullable|string|max:100',
            'skills' => 'nullable|string',
            'contract_type' => 'required|in:CDI,CDD,Interim,Stage',
            'hire_date' => 'required|date',
            'birth_date' => 'nullable|date',
            'base_salary' => 'nullable|numeric|min:0',
            'status' => 'required|in:active,inactive,leave',
            'cin' => 'nullable|string|max:20',
            'cnss' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'manager_id' => 'nullable|exists:employees,id',
            'user_id' => 'nullable|exists:users,id|unique:employees,user_id',
        ]);

        // Generate matricule
        $lastEmp = Employee::latest('id')->first();
        $validated['matricule'] = 'EMP' . str_pad(($lastEmp ? $lastEmp->id + 1 : 1), 4, '0', STR_PAD_LEFT);

        if ($request->hasFile('photo')) {
            $validated['photo'] = $request->file('photo')->store('photos', 'public');
        }

        Employee::create($validated);

        return redirect()->route('employees.index')
            ->with('success', 'Employé créé avec succès.');
    }

    public function show(Employee $employee)
    {
        $employee->load(['absences' => fn($q) => $q->latest()->take(10), 'salaries' => fn($q) => $q->latest()->take(6)]);
        return view('employees.show', compact('employee'));
    }

    public function edit(Employee $employee)
    {
        $managers = Employee::where('status', 'active')->where('id', '!=', $employee->id)->get();
        // Get users that are not already linked to employees (except the current one)
        $linkedUserIds = Employee::whereNotNull('user_id')->where('id', '!=', $employee->id)->pluck('user_id');
        $users = User::whereNotIn('id', $linkedUserIds)->get();
        return view('employees.edit', compact('employee', 'managers', 'users'));
    }

    public function update(Request $request, Employee $employee)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'email' => 'required|email|unique:employees,email,' . $employee->id,
            'phone' => 'nullable|string|max:20',
            'department' => 'required|string|max:100',
            'position' => 'required|string|max:100',
            'diploma_type' => 'nullable|string|max:100',
            'skills' => 'nullable|string',
            'contract_type' => 'required|in:CDI,CDD,Interim,Stage',
            'hire_date' => 'required|date',
            'birth_date' => 'nullable|date',
            'base_salary' => 'nullable|numeric|min:0',
            'status' => 'required|in:active,inactive,leave',
            'cin' => 'nullable|string|max:20',
            'cnss' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'manager_id' => 'nullable|exists:employees,id',
            'user_id' => 'nullable|exists:users,id|unique:employees,user_id,' . $employee->id,
        ]);

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
