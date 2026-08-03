<?php

namespace App\Services;

use App\Enums\UserRole;
use App\Http\Requests\StoreEmployeeRequest;
use App\Http\Requests\UpdateEmployeeRequest;
use App\Models\Department;
use App\Models\Employee;
use App\Models\User;
use App\Models\UserPermission;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class EmployeeService
{
    // =========================================================================
    // CREATE / UPDATE (bas niveau)
    // =========================================================================

    /**
     * Crée un employé à partir des données déjà validées/préparées.
     */
    public function create(array $data): Employee
    {
        return Employee::create($data);
    }

    /**
     * Met à jour un employé existant à partir des données déjà validées/préparées.
     */
    public function update(Employee $employee, array $data): Employee
    {
        $employee->update($data);

        return $employee;
    }

    // =========================================================================
    // LISTING / AJAX
    // =========================================================================

    public function getPaginatedEmployees(Request $request, int $perPage = 100)
    {
        return $this->buildQuery($request)
            ->with(['user', 'absences'])
            ->defaultOrder()
            ->paginate($perPage);
    }

    public function getAjaxEmployeesData(Request $request, int $perPage = 15): array
    {
        $page = $request->get('page', 1);

        $employees = $this->buildQuery($request)
            ->with(['user:id,name'])
            ->defaultOrder()
            ->paginate($perPage, ['*'], 'page', $page);

        return [
            'employees'  => $employees->map(fn ($e) => $this->formatEmployeeForAjax($e)),
            'pagination' => [
                'current_page' => $employees->currentPage(),
                'total'        => $employees->total(),
                'has_more'     => $employees->hasMorePages(),
            ],
        ];
    }

    private function formatEmployeeForAjax(Employee $e): array
    {
        return [
            'id'            => $e->id,
            'matricule'     => $e->matricule ?? 'N/A',
            'full_name'     => $e->full_name ?? 'N/A',
            'department'    => $e->department ?? 'N/A',
            'position'      => $e->position ?? '',
            'status_label'  => $e->status_label ?? ($e->status ?? 'N/A'),
            'status_color'  => $this->getStatusColor($e->status),
            'hire_date'     => $e->hire_date?->format('d/m/Y') ?? '',
            'contract_type' => $e->contract_type ?? '',
            'csrf_token'    => csrf_token(),
            '_method'       => 'DELETE',
            'base_salary'   => $e->base_salary ? number_format($e->base_salary, 0) : '0',
            'photo'         => $e->photo,
            'photo_url'     => $e->photo ? asset('storage/' . $e->photo) : null,
        ];
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

    // =========================================================================
    // REORDER
    // =========================================================================

    public function reorderEmployees(array $order): void
    {
        DB::transaction(function () use ($order) {
            foreach ($order as $index => $id) {
                Employee::where('id', $id)->update(['sort_order' => $index + 1]);
            }
        });
    }

    // =========================================================================
    // CREATE / EDIT FORM DATA
    // =========================================================================

    public function getCreateFormData(): array
    {
        return [
            'managers'    => Employee::active()->get(),
            'users'       => User::whereDoesntHave('employee')->get(),
            'departments' => $this->getDepartmentsList(),
            'roles'       => UserRole::cases(),
        ];
    }

    public function getEditFormData(Employee $employee): array
    {
        $linkedUser = null;
        if ($employee->user_id) {
            $linkedUser = User::with('modulePermissions')->find($employee->user_id);
        }

        return [
            'employee'    => $employee,
            'linkedUser'  => $linkedUser,
            'managers'    => Employee::active()->where('id', '!=', $employee->id)->get(),
            'users'       => User::whereDoesntHave('employee')
                ->when($employee->user_id, fn ($q) => $q->orWhere('id', $employee->user_id))
                ->get(),
            'departments' => $this->getDepartmentsList(),
            'roles'       => UserRole::cases(),
        ];
    }

    // =========================================================================
    // STORE
    // =========================================================================

    public function storeEmployee(StoreEmployeeRequest $request): Employee
    {
        return DB::transaction(function () use ($request) {
            $validated = $request->validated();

            $validated = $this->attachPhoto($request, $validated);
            $validated = $this->attachDocuments($request, $validated);

            $employee = $this->create($validated);

            if ($request->boolean('create_account')) {
                $this->createLinkedUserAccount($employee, $request);
            }

            return $employee;
        });
    }

    private function attachPhoto(Request $request, array $validated): array
    {
        if ($request->hasFile('photo')) {
            $validated['photo'] = $request->file('photo')->store('employees/photos', 'public');
        } else {
            unset($validated['photo']);
        }

        return $validated;
    }

    private function attachDocuments(Request $request, array $validated): array
    {
        foreach ($this->documentFields() as $field) {
            if ($request->hasFile($field)) {
                $validated[$field . '_path'] = $request->file($field)->store('employees/documents', 'public');
            }
            unset($validated[$field]);
        }

        return $validated;
    }

    private function createLinkedUserAccount(Employee $employee, Request $request): void
    {
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

        // Seul un Admin peut définir les permissions granulaires
        if (auth()->user()->isAdmin()) {
            $this->savePermissions($user, $request->input('permissions', []));
        }
    }

    // =========================================================================
    // SHOW
    // =========================================================================

    public function getEmployeeDetail(Employee $employee): Employee
    {
        $employee->load([
            'absences' => fn ($q) => $q->latest()->take(10),
            'salaries' => fn ($q) => $q->latest()->take(6),
        ]);

        $this->ensurePinExists($employee);

        return $employee;
    }

    private function ensurePinExists(Employee $employee): void
    {
        if (is_null($employee->plain_pin)) {
            $plainPin             = $this->generatePin();
            $employee->plain_pin  = $plainPin;
            $employee->pin        = Hash::make($plainPin);
            $employee->saveQuietly();
            Log::info("Generated PIN for employee {$employee->id}: {$plainPin}");
        }
    }

    private function generatePin(): string
    {
        return sprintf('%04d%s', rand(1000, 9999), chr(rand(65, 90)) . chr(rand(65, 90)));
    }

    // =========================================================================
    // UPDATE
    // =========================================================================

    public function updateEmployee(UpdateEmployeeRequest $request, Employee $employee): void
    {
        $validated = $request->validated();

        $validated = $this->replacePhoto($request, $employee, $validated);
        $validated = $this->replaceDocuments($request, $employee, $validated);

        // Bloquer conges_anterieurs pour non-admins AVANT l'update
        if (!auth()->user()->isAdmin()) {
            unset($validated['conges_anterieurs']);
        }

        // Nettoyer les champs mot de passe / rôle avant l'update employé
        unset(
            $validated['change_password'],
            $validated['new_password'],
            $validated['new_password_confirmation'],
            $validated['user_role']
        );

        $this->update($employee, $validated);

        $user = null;

        if ($this->shouldUpdatePassword($request)) {
            $user = $employee->user ?? ($employee->user_id ? User::find($employee->user_id) : null);

            if ($user) {
                $this->updateUserPassword($user, $request->new_password, $employee);
            } else {
                Log::warning('Tentative MàJ mot de passe sans compte lié', [
                    'employee_id' => $employee->id,
                ]);
            }
        }

        if ($this->shouldUpdateRole($request, $employee)) {
            $user = $user ?? ($employee->user ?? User::find($employee->user_id));

            if ($user) {
                $this->updateUserRole($user, $request->user_role, $employee);
            }
        }

        if ($this->shouldUpdatePermissions($request, $employee)) {
            $user = $user ?? User::find($employee->user_id);

            if ($user) {
                $this->savePermissions($user, $request->input('permissions', []));
                $user->clearPermCache();
            }
        }
    }

    private function shouldUpdatePassword(UpdateEmployeeRequest $request): bool
    {
        return auth()->user()->isAdmin()
            && $request->boolean('change_password')
            && $request->filled('new_password');
    }

    private function shouldUpdateRole(UpdateEmployeeRequest $request, Employee $employee): bool
    {
        return auth()->user()->isAdmin()
            && (bool) $employee->user_id
            && $request->filled('user_role');
    }

    private function shouldUpdatePermissions(UpdateEmployeeRequest $request, Employee $employee): bool
    {
        return $request->has('permissions')
            && (bool) $employee->user_id
            && auth()->user()->isAdmin();
    }

    private function replacePhoto(Request $request, Employee $employee, array $validated): array
    {
        if ($request->hasFile('photo')) {
            if ($employee->photo && Storage::disk('public')->exists($employee->photo)) {
                Storage::disk('public')->delete($employee->photo);
            }
            $validated['photo'] = $request->file('photo')->store('employees/photos', 'public');
        } else {
            unset($validated['photo']);
        }

        return $validated;
    }

    private function replaceDocuments(Request $request, Employee $employee, array $validated): array
    {
        foreach ($this->documentFields() as $field) {
            if ($request->hasFile($field)) {
                $oldPath = $employee->{$field . '_path'};
                if ($oldPath && Storage::disk('public')->exists($oldPath)) {
                    Storage::disk('public')->delete($oldPath);
                }
                $validated[$field . '_path'] = $request->file($field)->store('employees/documents', 'public');
            }
            unset($validated[$field]);
        }

        return $validated;
    }

    private function updateUserPassword(User $user, string $newPassword, Employee $employee): void
    {
        $user->password       = Hash::make($newPassword);
        $user->plain_password = $newPassword;
        $user->save();

        Log::info('Mot de passe mis à jour', [
            'user_id'     => $user->id,
            'employee_id' => $employee->id,
            'by_admin'    => auth()->id(),
        ]);
    }

    private function updateUserRole(User $user, string $requestedRole, Employee $employee): void
    {
        $previousRole = $user->role;
        $newRole      = $this->normalizeRole($requestedRole);

        if ($newRole === $previousRole) {
            return;
        }

        $user->role = $newRole;
        $user->save();

        Log::info('Rôle utilisateur mis à jour', [
            'user_id'      => $user->id,
            'employee_id'  => $employee->id,
            'ancien_role'  => $previousRole,
            'nouveau_role' => $newRole,
            'by_admin'     => auth()->id(),
        ]);
    }

    // =========================================================================
    // DESTROY
    // =========================================================================

    public function deleteEmployee(Employee $employee): void
    {
        $this->deleteEmployeeFiles($employee);

        $user = $this->findLinkedUser($employee);

        if ($user) {
            $user->verificationCodes()->delete();
            $user->modulePermissions()->delete();
            $user->delete();
        }

        $employee->delete();
    }

    private function deleteEmployeeFiles(Employee $employee): void
    {
        foreach ($this->documentPathFields() as $field) {
            if ($employee->$field && Storage::disk('public')->exists($employee->$field)) {
                Storage::disk('public')->delete($employee->$field);
            }
        }

        if ($employee->photo && Storage::disk('public')->exists($employee->photo)) {
            Storage::disk('public')->delete($employee->photo);
        }
    }

    private function findLinkedUser(Employee $employee): ?User
    {
        $tenantId = auth()->user()->tenant_id;

        if ($employee->user_id) {
            return User::find($employee->user_id);
        }

        if ($employee->email) {
            return User::where('email', $employee->email)
                ->where('tenant_id', $tenantId)
                ->first();
        }

        return null;
    }

    // =========================================================================
    // PIN
    // =========================================================================

    public function regeneratePin(Employee $employee): string
    {
        $plainPin            = $this->generatePin();
        $employee->plain_pin = $plainPin;
        $employee->pin       = Hash::make($plainPin);
        $employee->save();

        Log::info("Regenerated PIN for employee {$employee->id} ({$employee->full_name}): {$plainPin}");

        return $plainPin;
    }

    // =========================================================================
    // PDF EXPORTS
    // =========================================================================

    public function getEmployeesForPdfExport(Request $request): Collection
    {
        return $this->buildQuery($request)
            ->orderBy('department')
            ->get();
    }

    public function getEmployeesByDepartment(string $department): Collection
    {
        return Employee::where('department', $department)->get();
    }

    // =========================================================================
    // UNIQUENESS CHECK
    // =========================================================================

    public function checkFieldUniqueness(string $field, string $value, ?int $ignoreId): array
    {
        $tenantId = config('app.current_tenant_id') ?? auth()->user()->tenant_id;

        $taken = Employee::where('tenant_id', $tenantId)
            ->where($field, $value)
            ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
            ->exists();

        $label = $field === 'cin' ? 'CIN' : 'téléphone';

        return [
            'taken'   => $taken,
            'message' => $taken
                ? "Ce numéro de {$label} est déjà utilisé par un autre employé."
                : null,
        ];
    }

    // =========================================================================
    // HELPERS PRIVÉS PARTAGÉS
    // =========================================================================

    private function documentFields(): array
    {
        return ['doc_casier', 'doc_rib', 'doc_diplomes', 'doc_cin', 'doc_contrat'];
    }

    private function documentPathFields(): array
    {
        return ['doc_casier_path', 'doc_rib_path', 'doc_diplomes_path', 'doc_cin_path', 'doc_contrat_path'];
    }

    /**
     * Persiste les permissions du formulaire pour un utilisateur donné.
     * ⚠️ Cette méthode ne doit être appelée que si l'utilisateur courant
     * est Admin — la vérification est faite par les appelants.
     */
    private function savePermissions(User $user, array $rawPerms): void
    {
        UserPermission::where('user_id', $user->id)->delete();

        if (empty($rawPerms)) {
            return;
        }

        $rows = [];
        $now  = now();

        foreach ($rawPerms as $module => $actions) {
            if (
                empty($actions['view'])
                && empty($actions['create'])
                && empty($actions['edit'])
                && empty($actions['delete'])
            ) {
                continue;
            }

            $rows[] = [
                'user_id'    => $user->id,
                'module'     => $module,
                'can_view'   => !empty($actions['view']),
                'can_create' => !empty($actions['create']),
                'can_edit'   => !empty($actions['edit']),
                'can_delete' => !empty($actions['delete']),
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        if (!empty($rows)) {
            UserPermission::insert($rows);

            Log::info('Permissions sauvegardées', [
                'user_id' => $user->id,
                'modules' => array_column($rows, 'module'),
            ]);
        }
    }

    private function normalizeRole(?string $role): string
    {
        if (!$role) {
            return UserRole::Employee->value;
        }

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

    private function buildQuery(Request $request)
    {
        return Employee::query()
            ->when($request->get('filter') === 'active', fn ($q) => $q->active())
            ->when($request->get('filter') === 'inactive', fn ($q) => $q->status('inactive'))
            ->when($request->search, function ($q, $search) {
                $q->where(function ($q) use ($search) {
                    $q->where('first_name', 'like', "%$search%")
                      ->orWhere('last_name', 'like', "%$search%")
                      ->orWhere('matricule', 'like', "%$search%")
                      ->orWhere('email', 'like', "%$search%");
                });
            })
            ->when($request->department, fn ($q, $dep) => $q->where('department', $dep))
            ->when($request->status, fn ($q, $status) => $q->status($status));
    }

    public function getDepartmentsList()
    {
        try {
            $tenantId = auth()->user()->tenant_id;

            $departments = Department::where('tenant_id', $tenantId)
                ->orderBy('name')
                ->pluck('name');

            if ($departments->isNotEmpty()) {
                return $departments;
            }

        } catch (\Exception $e) {
            Log::warning('getDepartmentsList error: ' . $e->getMessage());
        }

        return Employee::whereNotNull('department')
            ->where('department', '!=', '')
            ->distinct()
            ->orderBy('department')
            ->pluck('department');
    }
}
