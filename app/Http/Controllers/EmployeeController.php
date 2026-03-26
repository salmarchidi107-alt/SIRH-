<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Exports\EmployeesExport;
use Maatwebsite\Excel\Facades\Excel;

class EmployeeController extends Controller
{
    public function index(Request $request)
    {
        $query = Employee::query();

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

        $currentUser = Auth::user();
        if (!$currentUser || !in_array($currentUser->role, ['admin', 'rh'])) {
            abort(403, 'Seuls les administrateurs et responsables RH peuvent créer des employés.');
        }

        $linkedUserIds = Employee::whereNotNull('user_id')->pluck('user_id');
        $users = User::whereNotIn('id', $linkedUserIds)->get();
        return view('employees.create', compact('managers', 'users', 'currentUser'));
    }

    public function store(Request $request)
    {
        $currentUser = Auth::user();
        if (!$currentUser || !in_array($currentUser->role, ['admin', 'rh'])) {
            abort(403, 'Seuls les administrateurs et responsables RH peuvent créer des employés.');
        }

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
            'children_count' => 'nullable|integer|min:0',
            'payment_method' => 'nullable|in:virement,cash,chèque',
            'bank' => 'nullable|string|max:100',
            'rib' => 'nullable|string|max:30',
            'contractual_benefits' => 'nullable|string',
            'create_account' => 'nullable|boolean',
            'user_role' => 'required_if:create_account,true|in:employee,rh,admin',
            'user_password' => 'required_if:create_account,true|min:8|confirmed',
        ]);

        if ($request->boolean('create_account')) {
            $userEmail = $validated['email'];
            if (User::where('email', $userEmail)->exists()) {
                return back()->withErrors(['email' => 'Email déjà utilisé pour un compte utilisateur.'])->withInput();
            }

            $user = User::create([
                'name' => $validated['first_name'] . ' ' . $validated['last_name'],
                'email' => $userEmail,
                'password' => Hash::make($request->user_password),
                'role' => $request->user_role,
            ]);
            $validated['user_id'] = $user->id;
        } elseif ($request->user_id) {
            $user = User::find($request->user_id);
            if ($user->employee_id) {
                return back()->withErrors(['user_id' => 'Ce compte est déjà lié à un employé.']);
            }
            $validated['user_id'] = $user->id;
        } else {
            $validated['user_id'] = null;
        }

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
            'children_count' => 'nullable|integer|min:0',
            'payment_method' => 'nullable|in:virement,cash,chèque',
            'bank' => 'nullable|string|max:100',
            'rib' => 'nullable|string|max:30',
            'contractual_benefits' => 'nullable|string',
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

    public function export()
    {
        return Excel::download(new EmployeesExport, 'employees.xlsx');
    }
}

