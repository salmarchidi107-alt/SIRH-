<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Http\Requests\StoreEmployeeRequest;
use App\Http\Requests\UpdateEmployeeRequest;
use App\Models\Department;
use App\Models\Employee;
use App\Models\User;
use App\Models\UserPermission;
use App\Services\EmployeeService;
use Illuminate\Http\Request;
use App\Exports\EmployeesExport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
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
                ->paginate(100);

            $departments = $this->getDepartmentsList();
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

    public function ajaxIndex(Request $request)
    {
        try {
            $perPage = 15;
            $page    = $request->get('page', 1);

            $employees = $this->buildQuery($request)
                ->with(['user:id,name'])
                ->defaultOrder()
                ->paginate($perPage, ['*'], 'page', $page);

            return response()->json([
                'employees' => $employees->map(fn($e) => [
                    'id'            => $e->id,
                    'matricule'     => $e->matricule     ?? 'N/A',
                    'full_name'     => $e->full_name     ?? 'N/A',
                    'department'    => $e->department    ?? 'N/A',
                    'position'      => $e->position      ?? '',
                    'status_label'  => $e->status_label  ?? ($e->status ?? 'N/A'),
                    'status_color'  => $this->getStatusColor($e->status),
                    'hire_date'     => $e->hire_date?->format('d/m/Y') ?? '',
                    'contract_type' => $e->contract_type ?? '',
                    'csrf_token'    => csrf_token(),
                    '_method'       => 'DELETE',
                    'base_salary'   => $e->base_salary ? number_format($e->base_salary, 0) : '0',
                ]),
                'pagination' => [
                    'current_page' => $employees->currentPage(),
                    'total'        => $employees->total(),
                    'has_more'     => $employees->hasMorePages(),
                ],
            ]);

        } catch (Exception $e) {
            Log::error('Employee ajaxIndex error', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Erreur chargement.'], 500);
        }
    }

    private function getStatusColor($status): string
    {
        return match ($status) {
            'active'   => 'success',
            'leave'    => 'warning',
            'inactive' => 'neutral',
            default    => 'error',
        };
    }

    private function normalizeRole(?string $role): string
    {
        if (!$role) return UserRole::Employee->value;

        foreach (UserRole::cases() as $case) {
            if ($case->value === strtolower(trim($role))) {
                return $case->value;
            }
        }

        foreach (UserRole::cases() as $case) {
            if (strtolower($case->label()) === strtolower(trim($role))) {
                return $case->value;
            }
        }

        Log::warning('Role inconnu reçu, fallback employee', ['role' => $role]);
        return UserRole::Employee->value;
    }

    public function reorder(Request $request)
    {
        try {
            $request->validate([
                'order'   => 'required|array',
                'order.*' => 'exists:employees,id',
            ]);

            DB::transaction(function () use ($request) {
                foreach ($request->order as $index => $id) {
                    Employee::where('id', $id)->update(['sort_order' => $index + 1]);
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
                'managers'    => Employee::active()->get(),
                'users'       => User::whereDoesntHave('employee')->get(),
                'departments' => $this->getDepartmentsList(),
                'roles'       => UserRole::cases(),
            ]);

        } catch (Exception $e) {
            Log::error('Employee create error', ['error' => $e->getMessage()]);
            abort(500, 'Erreur chargement formulaire.');
        }
    }

    public function store(StoreEmployeeRequest $request)
    {
        try {
            DB::transaction(function () use ($request) {

                $validated = $request->validated();

                // ── Stocker les pièces jointes PDF ──────────────────────
                $docFields = ['doc_casier', 'doc_rib', 'doc_diplomes', 'doc_cin', 'doc_contrat'];
                foreach ($docFields as $field) {
                    if ($request->hasFile($field)) {
                        $validated[$field . '_path'] = $request->file($field)
                            ->store('employees/documents', 'public');
                    }
                    unset($validated[$field]);
                }

                $employee = $this->employeeService->create($validated);

                if ($request->boolean('create_account')) {
                    $tenantId = config('app.current_tenant_id') ?? auth()->user()->tenant_id;
                    $role     = $this->normalizeRole($request->user_role);

                    Log::info('Création compte user', [
                        'email'      => $employee->email,
                        'role_recu'  => $request->user_role,
                        'role_final' => $role,
                    ]);

                    $user = User::firstOrCreate(
    [
        'email'     => $employee->email,
        'tenant_id' => $tenantId,
    ],
    [
        'name'           => $employee->first_name . ' ' . $employee->last_name,
        'password'       => Hash::make($request->user_password),
        'plain_password' => $request->user_password,
        'role'           => $role,
    ]
);

                    $employee->update(['user_id' => $user->id]);

                    // ── Sauvegarder les permissions ──────────────────────
                    $this->savePermissions($user, $request->input('permissions', []));
                }
            });

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
            $employee->load([
                'absences' => fn($q) => $q->latest()->take(10),
                'salaries' => fn($q) => $q->latest()->take(6),
            ]);

            if (is_null($employee->plain_pin)) {
                $plainPin            = sprintf('%04d%s', rand(1000, 9999), chr(rand(65, 90)) . chr(rand(65, 90)));
                $employee->plain_pin = $plainPin;
                $employee->pin       = Hash::make($plainPin);
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
            // Charger le compte utilisateur lié avec ses permissions (eager load)
            $linkedUser = null;
            if ($employee->user_id) {
                $linkedUser = User::with('modulePermissions')
                    ->find($employee->user_id);
            }

            return view('employees.edit', [
                'employee'    => $employee,
                'linkedUser'  => $linkedUser,
                'managers'    => Employee::active()->where('id', '!=', $employee->id)->get(),
                'users'       => User::whereDoesntHave('employee')
                    ->when($employee->user_id, fn($q) => $q->orWhere('id', $employee->user_id))
                    ->get(),
                'departments' => $this->getDepartmentsList(),
                'roles'       => UserRole::cases(),
            ]);

        } catch (Exception $e) {
            Log::error('Employee edit error', ['error' => $e->getMessage()]);
            abort(500, 'Erreur chargement formulaire.');
        }
    }

    public function update(UpdateEmployeeRequest $request, Employee $employee)
    {
        try {
            $validated = $request->validated();

            // ── Pièces jointes PDF ───────────────────────────────────────
            $docFields = ['doc_casier', 'doc_rib', 'doc_diplomes', 'doc_cin', 'doc_contrat'];
            foreach ($docFields as $field) {
                if ($request->hasFile($field)) {
                    $oldPath = $employee->{$field . '_path'};
                    if ($oldPath && Storage::disk('public')->exists($oldPath)) {
                        Storage::disk('public')->delete($oldPath);
                    }
                    $validated[$field . '_path'] = $request->file($field)
                        ->store('employees/documents', 'public');
                }
                unset($validated[$field]);
            }

            // ── Nettoyage des champs mot de passe avant update employé ───
            unset($validated['change_password'], $validated['new_password'], $validated['new_password_confirmation']);

            $this->employeeService->update($employee, $validated);

            // ── Mise à jour du mot de passe ──────────────────────────────
            if ($request->boolean('change_password') && $request->filled('new_password')) {

                // Récupérer ou créer le compte utilisateur lié
                $user = $employee->user;

                if (! $user && $employee->user_id) {
                    $user = User::find($employee->user_id);
                }

                if ($user) {
                    $user->password       = $request->new_password;
                    $user->plain_password = $request->new_password;
                    $user->save();

                    Log::info('Mot de passe mis à jour', [
                        'user_id'     => $user->id,
                        'employee_id' => $employee->id,
                    ]);
                } else {
                    // Aucun compte lié — impossible de définir un mot de passe
                    Log::warning('Tentative de mise à jour du mot de passe sans compte utilisateur lié', [
                        'employee_id' => $employee->id,
                    ]);
                }
            }

            // ── Permissions ──────────────────────────────────────────────
            if ($request->has('permissions') && $employee->user_id) {
                $user = $user ?? User::find($employee->user_id);
                if ($user) {
                    $this->savePermissions($user, $request->input('permissions', []));
                    $user->clearPermCache();
                }
            }

            return redirect()->route('employees.show', $employee)
                ->with('success', 'Employé mis à jour avec succès.');

        } catch (Exception $e) {
            Log::error('Employee update error', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return back()->withErrors(['error' => 'Erreur mise à jour : ' . $e->getMessage()])->withInput();
        }
    }

    public function destroy(Employee $employee)
    {
        try {
            $docFields = ['doc_casier_path', 'doc_rib_path', 'doc_diplomes_path', 'doc_cin_path', 'doc_contrat_path'];
            foreach ($docFields as $field) {
                if ($employee->$field && Storage::disk('public')->exists($employee->$field)) {
                    Storage::disk('public')->delete($employee->$field);
                }
            }

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

    public function regeneratePin(Request $request, Employee $employee)
    {
        abort_unless(auth()->user()->can('manage_employees'), 403);

        $plainPin            = sprintf('%04d%s', rand(1000, 9999), chr(rand(65, 90)) . chr(rand(65, 90)));
        $employee->plain_pin = $plainPin;
        $employee->pin       = Hash::make($plainPin);
        $employee->save();

        Log::info("Regenerated PIN for employee {$employee->id} ({$employee->full_name}): {$plainPin}");

        return response()->json([
            'success' => true,
            'pin'     => $plainPin,
            'message' => 'PIN regénéré avec succès !',
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

    // =========================================================================
    // HELPERS PRIVÉS
    // =========================================================================

    /**
     * Persiste les permissions du formulaire pour un utilisateur donné.
     * Efface et recrée toutes les permissions en une seule opération.
     *
     * @param  User   $user
     * @param  array  $rawPerms  Ex: ['employees' => ['view' => 'on', 'create' => 'on'], ...]
     */
    private function savePermissions(User $user, array $rawPerms): void
    {
        // Suppression complète des anciennes permissions
        UserPermission::where('user_id', $user->id)->delete();

        if (empty($rawPerms)) {
            return;
        }

        $rows = [];
        $now  = now();

        foreach ($rawPerms as $module => $actions) {
            // On ne sauvegarde que si au moins une action est cochée
            if (
                empty($actions['view'])   &&
                empty($actions['create']) &&
                empty($actions['edit'])   &&
                empty($actions['delete'])
            ) {
                continue;
            }

            $rows[] = [
                'user_id'    => $user->id,
                'module'     => $module,
                'can_view'   => ! empty($actions['view']),
                'can_create' => ! empty($actions['create']),
                'can_edit'   => ! empty($actions['edit']),
                'can_delete' => ! empty($actions['delete']),
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        if (! empty($rows)) {
            UserPermission::insert($rows);

            Log::info('Permissions sauvegardées', [
                'user_id' => $user->id,
                'modules' => array_column($rows, 'module'),
            ]);
        }
    }

    private function buildQuery(Request $request)
    {
        return Employee::query()
            ->when($request->get('filter') === 'active', fn($q) => $q->active())
            ->when($request->search, function ($q, $search) {
                $q->where(function ($q) use ($search) {
                    $q->where('first_name', 'like', "%$search%")
                      ->orWhere('last_name',  'like', "%$search%")
                      ->orWhere('matricule',  'like', "%$search%")
                      ->orWhere('email',      'like', "%$search%");
                });
            })
            ->when($request->department, fn($q, $dep)    => $q->where('department', $dep))
            ->when($request->status,     fn($q, $status) => $q->status($status));
    }

    private function getDepartmentsList()
    {
        try {
            $tenantId = auth()->user()->tenant_id;

            $departments = Department::where('tenant_id', $tenantId)
                ->orderBy('name')
                ->pluck('name');

            if ($departments->isNotEmpty()) {
                return $departments;
            }

        } catch (Exception $e) {
            Log::warning('getDepartmentsList error: ' . $e->getMessage());
        }

        return Employee::whereNotNull('department')
            ->where('department', '!=', '')
            ->distinct()
            ->orderBy('department')
            ->pluck('department');
    }
}
