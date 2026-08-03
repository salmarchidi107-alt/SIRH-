<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreEmployeeRequest;
use App\Http\Requests\UpdateEmployeeRequest;
use App\Models\Employee;
use App\Services\EmployeeService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Exports\EmployeesExport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Log;
use Exception;
use Barryvdh\DomPDF\Facade\Pdf;

class EmployeeController extends Controller
{
    public function __construct(private EmployeeService $employeeService) {}

    public function index(Request $request)
    {
        try {
            $employees   = $this->employeeService->getPaginatedEmployees($request);
            $departments = $this->employeeService->getDepartmentsList();
            $filter      = $request->get('filter', 'all');

            return view('employees.index', compact('employees', 'departments', 'filter'));

        } catch (Exception $e) {
            Log::error('Employee index error', ['error' => $e->getMessage()]);
            return view('employees.index', [
                'employees'   => collect(),
                'departments' => collect(),
                'filter'      => 'all',
                'error'       => 'Erreur chargement employés.',
            ]);
        }
    }

    public function ajaxIndex(Request $request): JsonResponse
    {
        try {
            $data = $this->employeeService->getAjaxEmployeesData($request);

            return response()->json($data);

        } catch (Exception $e) {
            Log::error('Employee ajaxIndex error', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Erreur chargement.'], 500);
        }
    }

    public function reorder(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'order'   => 'required|array',
                'order.*' => 'exists:employees,id',
            ]);

            $this->employeeService->reorderEmployees($request->order);

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

            return view('employees.create', $this->employeeService->getCreateFormData());

        } catch (Exception $e) {
            Log::error('Employee create error', ['error' => $e->getMessage()]);
            abort(500, 'Erreur chargement formulaire.');
        }
    }

    public function store(StoreEmployeeRequest $request)
    {
        try {
            $this->employeeService->storeEmployee($request);

            return redirect()->route('employees.index')
                ->with('success', 'Employé créé avec succès.');

        } catch (\RuntimeException $e) {
            return back()->withErrors(['user' => $e->getMessage()])->withInput();

        } catch (Exception $e) {
            Log::error('Employee store error', ['error' => $e->getMessage()]);
            return back()->withErrors(['error' => 'Erreur création employé : ' . $e->getMessage()])->withInput();
        }
    }

    public function show(Employee $employee)
    {
        if (auth()->user()->role === 'employee' && auth()->user()->employee_id != $employee->id) {
            abort(403, 'Accès restreint. Vous ne pouvez voir que votre propre profil.');
        }

        try {
            $employee = $this->employeeService->getEmployeeDetail($employee);

            return view('employees.show', compact('employee'));

        } catch (Exception $e) {
            Log::error('Employee show error', ['error' => $e->getMessage()]);
            abort(404, 'Employé non trouvé.');
        }
    }

    public function edit(Employee $employee)
    {
        try {
            return view('employees.edit', $this->employeeService->getEditFormData($employee));

        } catch (Exception $e) {
            Log::error('Employee edit error', ['error' => $e->getMessage()]);
            abort(500, 'Erreur chargement formulaire.');
        }
    }

    public function update(UpdateEmployeeRequest $request, Employee $employee)
    {
        try {
            $this->employeeService->updateEmployee($request, $employee);

            return redirect()->route('employees.show', $employee)
                ->with('success', 'Employé mis à jour avec succès.');

        } catch (Exception $e) {
            Log::error('Employee update error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return back()
                ->withErrors(['error' => 'Erreur mise à jour : ' . $e->getMessage()])
                ->withInput();
        }
    }

    public function destroy(Employee $employee)
    {
        try {
            $this->employeeService->deleteEmployee($employee);

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

    public function regeneratePin(Request $request, Employee $employee): JsonResponse
    {
        abort_unless(auth()->user()->can('manage_employees'), 403);

        $plainPin = $this->employeeService->regeneratePin($employee);

        return response()->json([
            'success' => true,
            'pin'     => $plainPin,
            'message' => 'PIN regénéré avec succès !',
        ]);
    }

    public function exportPdf(Request $request)
    {
        try {
            $employees = $this->employeeService->getEmployeesForPdfExport($request);
            $total     = $employees->count();

            if ($total === 0) {
                return back()->with('error', 'Aucun employé à exporter.');
            }

            $generatedAt = now()->format('d/m/Y à H:i');
            $filename    = 'employes_' . now()->format('Y-m-d_H-i') . '.pdf';

            $pdf = Pdf::loadView('pdf.employees', compact('employees', 'total', 'generatedAt'));
            return $pdf->download($filename);

        } catch (Exception $e) {
            Log::error('PDF export error', ['error' => $e->getMessage()]);
            return back()->with('error', 'Erreur génération PDF : ' . $e->getMessage());
        }
    }

    public function checkUnique(Request $request): JsonResponse
    {
        $ignoreId = $request->integer('ignore_id') ?: null;

        foreach (['cin', 'phone'] as $field) {
            if ($request->has($field)) {
                $value = trim($request->input($field));

                if (!$value) {
                    return response()->json(['taken' => false]);
                }

                return response()->json(
                    $this->employeeService->checkFieldUniqueness($field, $value, $ignoreId)
                );
            }
        }

        return response()->json(['taken' => false]);
    }

    public function exportPdfByDept(Request $request, string $department)
    {
        try {
            $employees = $this->employeeService->getEmployeesByDepartment($department);
            $total     = $employees->count();

            if ($total === 0) {
                return back()->with('error', 'Aucun employé dans ce département.');
            }

            $generatedAt = now()->format('d/m/Y à H:i');
            $filename    = 'employes-' . \Str::slug($department) . '_' . now()->format('Y-m-d') . '.pdf';

            $pdf = Pdf::loadView('pdf.employees', compact('employees', 'total', 'generatedAt'));
            return $pdf->download($filename);

        } catch (Exception $e) {
            Log::error('PDF dept export error', ['error' => $e->getMessage()]);
            return back()->with('error', 'Erreur génération PDF.');
        }
    }

    public function ajax(Request $request)
    {
        return $this->ajaxIndex($request);
    }
}
