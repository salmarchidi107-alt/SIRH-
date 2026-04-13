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
