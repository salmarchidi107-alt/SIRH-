<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Exception;

class EmployeeService
{
    public function create(array $data): Employee
    {
        try {
            // Résolution du tenant_id AVANT tout le reste
            $tenantId = config('app.current_tenant_id')
                ?? (auth()->check() ? auth()->user()->tenant_id : null);

            if (is_null($tenantId)) {
                throw new \RuntimeException('Tenant introuvable. Veuillez vous reconnecter.');
            }

            $data['tenant_id'] = $tenantId;

            // Prépare le compte user (en passant le tenant_id)
            $data = $this->prepareAccountPayload($data, $tenantId);

            $data['work_days'] = $data['work_days'] ?? [];

            if (!empty($data['photo']) && $data['photo'] instanceof UploadedFile) {
                $data['photo'] = $this->storePhoto($data['photo']);
            }

            if (!empty($data['pin'])) {
                $data['pin'] = Hash::make($data['pin']);
            }

            $data['matricule'] = $this->generateMatricule();

            $employee = Employee::create($data);

            Log::info('Employee created', [
                'employee_id' => $employee->id,
                'tenant_id'   => $employee->tenant_id,
                'created_by'  => auth()->id(),
            ]);

            return $employee;

        } catch (Exception $e) {
            Log::error('EmployeeService create error: ' . $e->getMessage());
            throw $e;
        }
    }

    public function update(Employee $employee, array $data): Employee
    {
        try {
            // Sécurité : on ne touche jamais au tenant_id lors d'une mise à jour
            unset($data['tenant_id']);

            if (!empty($data['photo']) && $data['photo'] instanceof UploadedFile) {
                $data['photo'] = $this->storePhoto($data['photo']);
            }

            $employee->update($data);

            return $employee;

        } catch (Exception $e) {
            Log::error('EmployeeService update error: ' . $e->getMessage(), [
                'employee_id' => $employee->id,
                'trace'       => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    protected function prepareAccountPayload(array $data, string $tenantId): array
    {
        try {
            if (!empty($data['create_account'])) {

                if (User::where('email', $data['email'])->exists()) {
                    throw new \RuntimeException('Email déjà utilisé pour un compte utilisateur.');
                }

                $user = User::create([
                    'name'      => trim($data['first_name'] . ' ' . $data['last_name']),
                    'email'     => $data['email'],
                    'password'  => Hash::make($data['user_password']),
                    'role'      => $data['user_role'],
                    'tenant_id' => $tenantId,
                ]);

                $data['user_id'] = $user->id;

            } elseif (!empty($data['user_id'])) {

                $user = User::find($data['user_id']);

                if (!$user) {
                    throw new \RuntimeException('Compte utilisateur introuvable.');
                }

                if (!empty($user->employee_id)) {
                    throw new \RuntimeException('Ce compte est déjà lié à un employé.');
                }

                $data['user_id'] = $user->id;

            } else {
                $data['user_id'] = null;
            }

            unset($data['create_account'], $data['user_password'], $data['user_password_confirmation']);

            return $data;

        } catch (Exception $e) {
            Log::error('EmployeeService prepareAccountPayload error: ' . $e->getMessage());
            throw $e;
        }
    }

    protected function generateMatricule(): string
    {
        try {
            // withoutGlobalScopes() obligatoire : sinon filtré par tenant
            $lastEmployee = Employee::withoutGlobalScopes()->latest('id')->first();
            $nextId = $lastEmployee ? $lastEmployee->id + 1 : 1;

            return config('constants.employee.matricule_prefix')
                . str_pad($nextId, config('constants.employee.matricule_padding'), '0', STR_PAD_LEFT);

        } catch (Exception $e) {
            Log::error('EmployeeService generateMatricule error: ' . $e->getMessage());
            throw new \RuntimeException('Erreur génération matricule.');
        }
    }

    protected function storePhoto(UploadedFile $photo): string
    {
        try {
            return $photo->store('photos', 'public');
        } catch (Exception $e) {
            Log::error('EmployeeService storePhoto error: ' . $e->getMessage());
            throw new \RuntimeException('Erreur upload photo.');
        }
    }
}