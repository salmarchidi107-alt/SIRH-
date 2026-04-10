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
            $data = $this->prepareAccountPayload($data);

            if (!empty($data['photo']) && $data['photo'] instanceof UploadedFile) {
                $data['photo'] = $this->storePhoto($data['photo']);
            }

            // Hash PIN si fourni
            if (!empty($data['pin'])) {
                $data['pin'] = Hash::make($data['pin']);
            }

            $data['matricule'] = $this->generateMatricule();

            return Employee::create($data);
        } catch (Exception $e) {
            Log::error('EmployeeService create error: ' . $e->getMessage(), ['data' => $data, 'trace' => $e->getTraceAsString()]);
            throw $e; // Rethrow for controller handling
        }
    }

    public function update(Employee $employee, array $data): Employee
    {
        try {
            if (!empty($data['photo']) && $data['photo'] instanceof UploadedFile) {
                $data['photo'] = $this->storePhoto($data['photo']);
            }

            $employee->update($data);

            return $employee;
        } catch (Exception $e) {
            Log::error('EmployeeService update error: ' . $e->getMessage(), ['employee_id' => $employee->id, 'trace' => $e->getTraceAsString()]);
            throw $e;
        }
    }

    protected function prepareAccountPayload(array $data): array
    {
        try {
            if (!empty($data['create_account'])) {
                if (User::where('email', $data['email'])->exists()) {
                    throw new \RuntimeException('Email déjà utilisé pour un compte utilisateur.');
                }

                $user = User::create([
                    'name' => trim($data['first_name'] . ' ' . $data['last_name']),
                    'email' => $data['email'],
                    'password' => Hash::make($data['user_password']),
                    'role' => $data['user_role'],
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
            $lastEmployee = Employee::latest('id')->first();
            $nextId = $lastEmployee ? $lastEmployee->id + 1 : 1;

            return config('constants.employee.matricule_prefix') . str_pad($nextId, config('constants.employee.matricule_padding'), '0', STR_PAD_LEFT);
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

