<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreEmployeeRequest;
use App\Http\Requests\UpdateEmployeeRequest;
use App\Models\Department;
use App\Models\Employee;
use App\Models\User;
use App\Services\EmployeeService;
use Illuminate\Http\Request;
use App\Exports\EmployeesExport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Exception;
use Barryvdh\DomPDF\Facade\Pdf;

class EmployeeController extends Controller
{
    public function __construct(private EmployeeService $employeeService) {}

    public function index(Request $request)
    {
        try {
            $employees = $this->buildQuery($request)
                ->with(['user', 'absences'])
                ->defaultOrder()
                ->paginate(15);

            $departments = Department::names();
            $filter = $request->get('filter', 'all');

            return view('employees.index', compact('employees', 'departments', 'filter'));

        } catch (Exception $e) {
            Log::error('Employee index error', ['error' => $e->getMessage()]);
            return view('employees.index', [
                'employees' => collect(),
                'error' => 'Erreur chargement employés.'
            ]);
        }
    }

    public function ajaxIndex(Request $request)
    {
        try {
            $employees = $this->buildQuery($request)
                ->with(['user']) // prevent N+1
                ->defaultOrder()
                ->get();

            return response()->json([
                'data' => $employees->map(fn($e) => [
                    'id' => $e->id,
                    'matricule' => $e->matricule,
                    'full_name' => $e->full_name,
                    'department' => $e->department,
                    'position' => $e->position,
                    'status_label' => $e->status_label,
                    'status_color' => $this->employeeService->getStatusColor($e->status),
                    'hire_date' => $e->hire_date?->format('d/m/Y'),
                    'base_salary' => number_format($e->base_salary, 0),
                ])
            ]);

        } catch (Exception $e) {
            Log::error('Employee ajaxIndex error', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Erreur chargement.'], 500);
        }
    }

    public function reorder(Request $request)
    {
        try {
            $request->validate([
                'order' => 'required|array',
                'order.*' => 'exists:employees,id'
            ]);

            DB::transaction(function () use ($request) {
                foreach ($request->order as $index => $id) {
                    Employee::where('id', $id)
                        ->update(['sort_order' => $index + 1]);
                }
            });

            return response()->json(['success' => true]);

        } catch (Exception $e) {
            Log::error('Employee reorder error', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Erreur réorganisation.'], 500);
        }
    }

    public function create()
    {
        try {
            abort_unless(auth()->user()->can('manage_employees'), 403);

            return view('employees.create', [
                'managers' => Employee::active()->get(),
                'users' => User::whereDoesntHave('employee')->get()
            ]);

        } catch (Exception $e) {
            Log::error('Employee create error', ['error' => $e->getMessage()]);
            abort(500, 'Erreur chargement formulaire.');
        }
    }

    public function store(StoreEmployeeRequest $request)
    {
        try {
            $this->employeeService->create($request->validated());

            return redirect()->route('employees.index')
                ->with('success', 'Employé créé avec succès.');

        } catch (\RuntimeException $e) {
            return back()->withErrors(['user' => $e->getMessage()])->withInput();

        } catch (Exception $e) {
            Log::error('Employee store error', ['error' => $e->getMessage()]);
            return back()->withErrors(['error' => 'Erreur création employé'])->withInput();
        }
    }

    public function show(Employee $employee)
    {
        // Server-side authorization
        if (auth()->user()->role === 'employee' && auth()->user()->employee_id != $employee->id) {
            abort(403, 'Accès restreint. Vous ne pouvez voir que votre propre profil.');
        }

        try {
            $employee->load([
                'absences' => fn($q) => $q->latest()->take(10),
                'salaries' => fn($q) => $q->latest()->take(6)
            ]);

            // Generate PIN only if NULL (preserve existing)
            if (is_null($employee->plain_pin)) {
                $plainPin = sprintf('%04d%s', rand(1000, 9999), chr(rand(65, 90)).chr(rand(65, 90)));
                $employee->plain_pin = $plainPin;
$employee->pin = \Illuminate\Support\Facades\Hash::make($plainPin);
                $employee->saveQuietly();
                Log::info("Generated PIN for employee {$employee->id}: {$plainPin}");
            }

            return view('employees.show', compact('employee'));

        } catch (Exception $e) {
            Log::error('Employee show error', ['error' => $e->getMessage()]);
            abort(404, 'Employé non trouvé.');
        }
    }

    public function edit(Employee $employee)
    {
        try {
            return view('employees.edit', [
                'employee' => $employee,
                'managers' => Employee::active()
                    ->where('id', '!=', $employee->id)
                    ->get(),
                'users' => User::whereDoesntHave('employee')
                    ->when($employee->user_id, fn($q) => $q->orWhere('id', $employee->user_id))
                    ->get()
            ]);

        } catch (Exception $e) {
            Log::error('Employee edit error', ['error' => $e->getMessage()]);
            abort(500, 'Erreur chargement formulaire.');
        }
    }

    public function update(UpdateEmployeeRequest $request, Employee $employee)
    {
        try {
            $this->employeeService->update($employee, $request->validated());

            return redirect()->route('employees.show', $employee)
                ->with('success', 'Employé mis à jour avec succès.');

        } catch (Exception $e) {
            Log::error('Employee update error', ['error' => $e->getMessage()]);
            return back()->withErrors(['error' => 'Erreur mise à jour'])->withInput();
        }
    }

    public function destroy(Employee $employee)
    {
        try {
            $employee->delete();

            return redirect()->route('employees.index')
                ->with('success', 'Employé supprimé.');

        } catch (Exception $e) {
            Log::error('Employee delete error', ['error' => $e->getMessage()]);
            return back()->with('error', 'Erreur suppression employé.');
        }
    }

    public function export()
    {
        try {
            return Excel::download(new EmployeesExport, 'employees.xlsx');

        } catch (Exception $e) {
            Log::error('Employee export error', ['error' => $e->getMessage()]);
            return back()->with('error', 'Erreur export Excel.');
        }
    }

    /**
     * Regenerate PIN for badge access (admin/rh only)
     */
    public function regeneratePin(Request $request, Employee $employee)
    {
        abort_unless(auth()->user()->can('manage_employees'), 403);

        $plainPin = sprintf('%04d%s', rand(1000, 9999), chr(rand(65, 90)).chr(rand(65, 90)));
        
        $employee->plain_pin = $plainPin;
        $employee->pin = \Illuminate\Support\Facades\Hash::make($plainPin);
        $employee->save();

        \Illuminate\Support\Facades\Log::info("Regenerated PIN for employee {$employee->id} ({$employee->full_name}): {$plainPin}");

        return response()->json([
            'success' => true,
            'pin' => $plainPin,
            'message' => 'PIN regénéré avec succès !'
        ]);
    }
public function exportPdf(Request $request)
{
    try {
        $employees = $this->buildQuery($request)
            ->orderBy('department')
            ->get();

        $total       = $employees->count();
        $generatedAt = now()->format('d/m/Y à H:i');
        $filename    = 'employes_' . now()->format('Y-m-d_H-i') . '.pdf';

        if ($total === 0) {
            return back()->with('error', 'Aucun employé à exporter.');
        }

        $pdf = Pdf::loadView('pdf.employees', compact('employees', 'total', 'generatedAt'));
        return $pdf->download($filename);

    } catch (Exception $e) {
        Log::error('PDF export error', ['error' => $e->getMessage()]);
        return back()->with('error', 'Erreur génération PDF : ' . $e->getMessage());
    }
}
   public function exportPdfByDept(Request $request, string $department)
{
    try {
        $employees = Employee::where('department', $department)->get();
        $total = $employees->count();

        if ($total === 0) {
            return back()->with('error', 'Aucun employé dans ce département.');
        }

        $generatedAt = now()->format('d/m/Y à H:i');
        $filename = 'employes-' . \Str::slug($department) . '_' . now()->format('Y-m-d') . '.pdf';

        $pdf = Pdf::loadView('pdf.employees', compact('employees', 'total', 'generatedAt'));
        return $pdf->download($filename);

    } catch (Exception $e) {
        Log::error('PDF dept export error', ['error' => $e->getMessage()]);
        return back()->with('error', 'Erreur génération PDF.');
    }
}
    /**
     * Reusable query builder (DRY)
     */
    private function buildQuery(Request $request)
    {
        return Employee::query()
            ->when($request->get('filter') === 'active', fn($q) => $q->active())
            ->when($request->search, function ($q, $search) {
                $q->where(function ($q) use ($search) {
                    $q->where('first_name', 'like', "%$search%")
                      ->orWhere('last_name', 'like', "%$search%")
                      ->orWhere('matricule', 'like', "%$search%")
                      ->orWhere('email', 'like', "%$search%");
                });
            })
            ->when($request->department, fn($q, $dep) => $q->where('department', $dep))
            ->when($request->status, fn($q, $status) => $q->status($status));
    }
}
