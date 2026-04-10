<?php

namespace App\Services\Employee;

use App\Models\Department;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class EmployeeCreator
{
    public function create(array $validated, Request $request): Employee
    {
        return DB::transaction(function () use ($validated, $request) {

            // ── 1. Département string → Department model ──────────────────────
            if (filled($validated['department'] ?? null)) {
                $deptName   = trim($validated['department']);
                $department = Department::firstOrCreate(
                    [
                        'name'      => $deptName,
                        'tenant_id' => config('app.current_tenant_id'),
                    ],
                    ['slug' => Str::slug($deptName)]
                );
                $validated['department_id'] = $department->id;
            }

            // ── 2. Photo ──────────────────────────────────────────────────────
            if ($request->hasFile('photo')) {
                $validated['photo'] = $request->file('photo')->store('photos', 'public');
            }

            // ── 3. skills → JSON (contrainte json_valid dans MySQL) ───────────
            if (!empty($validated['skills'])) {
                $validated['skills'] = json_encode([$validated['skills']]);
            } else {
                $validated['skills'] = null;
            }

            // ── 4. work_days → JSON AVANT create() ───────────────────────────
            if (!empty($validated['work_days']) && is_array($validated['work_days'])) {
                $validated['work_days'] = json_encode($validated['work_days']);
            } else {
                $validated['work_days'] = null;
            }

            // ── 5. Nettoyer les champs non-colonnes ───────────────────────────
            unset(
                $validated['department'],
                $validated['create_account'],
                $validated['user_role'],
                $validated['user_password'],
                $validated['user_password_confirmation']
            );

            // ── 6. Tenant ID + matricule temporaire ───────────────────────────
            $tenantId = config('app.current_tenant_id');
            $validated['tenant_id'] = $tenantId;
            $validated['matricule']  = 'TMP-' . Str::random(8);

            // ── 7. Création employee ──────────────────────────────────────────
            $employee = Employee::create($validated);

            // ── 8. Matricule final basé sur l'ID réel ─────────────────────────
            $employee->update([
                'matricule' => 'EMP' . str_pad($employee->id, 4, '0', STR_PAD_LEFT)
            ]);

            // ── 9. Compte utilisateur optionnel ──────────────────────────────
            if ($request->boolean('create_account') && filled($request->input('user_role'))) {
                if (User::where('email', $validated['email'])->exists()) {
                    throw ValidationException::withMessages([
                        'admin_email' => 'Cet email est déjà utilisé pour un compte utilisateur.'
                    ]);
                }

                $user = User::create([
                    'name'      => trim($validated['first_name'] . ' ' . $validated['last_name']),
                    'email'     => $validated['email'],
                    'password'  => Hash::make($request->input('user_password')),
                    'role'      => $request->input('user_role'),
                    'tenant_id' => $tenantId,
                ]);

                $employee->update(['user_id' => $user->id]);
            }

            return $employee->fresh();
        });
    }
}
