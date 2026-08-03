<?php

namespace App\Services\Auth;

use App\Models\Employee;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Stancl\Tenancy\Database\Models\Domain;


class LoginService
{

    public function attempt(array $credentials): bool
    {
        return Auth::attempt($credentials);
    }

    public function prepareAuthenticatedUser(): User
    {
        $user = auth()->user();

        $this->ensureRole($user);
        $this->linkEmployee($user);
        $this->resetTenancy();

        return $user;
    }

    private function ensureRole(User $user): void
    {
        // Rôle manquant → fallback employee
        if (! $user->role) {
            $user->role = User::ROLE_EMPLOYEE;
            $user->save();
        }
    }

    private function linkEmployee(User $user): void
    {
        // Lier l'employee si pas encore fait
        if ($user->employee_id) {
            return;
        }

        $employee = Employee::where('email', $user->email)->first();
        if (! $employee) {
            return;
        }

        $employee->user_id = $user->id;
        $employee->save();

        $user->employee_id = $employee->id;
        $user->save();
    }

    private function resetTenancy(): void
    {
        // Réinitialiser toute tenancy active
        if (tenancy()->initialized) {
            tenancy()->end();
        }
    }

    public function logout(): void
    {
        $this->resetTenancy();
        Auth::logout();
    }

    public function getTenantBrandingForCurrentDomain(): ?array
    {
        try {
            $domain       = request()->getHost();
            $tenantDomain = Domain::where('domain', $domain)->first();

            if (! $tenantDomain) {
                return null;
            }

            $tenant = Tenant::find($tenantDomain->tenant_id);

            if (! $tenant) {
                return null;
            }

            return [
                'name'        => $tenant->name,
                'brand_color' => $tenant->brand_color ?? '#1a8fa5',
                'logo_path'   => $tenant->logo_path ?? null,
            ];
        } catch (\Throwable $e) {
            return null;
        }
    }
}
