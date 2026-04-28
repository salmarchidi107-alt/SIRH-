<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;


class User extends Authenticatable
{

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'tenant_id',
        'employee_id',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password'          => 'hashed',
        'tenant_id'         => 'string',
    ];

    public function tenant()
    {
return $this->belongsTo(\App\Models\Tenant::class, 'tenant_id');
    }

    public function employee()
    {
        return $this->hasOne(Employee::class, 'user_id');
    }

    public function getEmployeeByLegacyKeyAttribute()
    {
        return Employee::where('user_id', $this->id)->first();
    }

    public function scopeTenant($query)
    {
        $tenantId = config('app.current_tenant_id');
        return $tenantId ? $query->where('tenant_id', $tenantId) : $query;
    }

    // ─── Roles Constants ────────────────────────────────────────────────────────

    const ROLE_ADMIN = 'admin';
    const ROLE_EMPLOYEE = 'employee';
    const ROLE_SUPERADMIN = 'superadmin';

    public function isSuperAdmin(): bool
    {
        return $this->role === self::ROLE_SUPERADMIN || is_null($this->tenant_id);
    }

    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    public function isRh(): bool
    {
        return $this->role === 'rh';
    }

    public function isAdminOrRh(): bool
{
    return in_array($this->role, ['admin', 'rh']);
}
    public function isEmployee(): bool
    {
        return $this->role === self::ROLE_EMPLOYEE;
    }

    public function getRoleDisplayName(): string
    {
        return match($this->role) {
            self::ROLE_SUPERADMIN => 'Super Administrateur',
            self::ROLE_ADMIN => 'Administrateur',
            'rh' => 'Responsable RH',
            self::ROLE_EMPLOYEE => 'Employé',
            default => 'Employé',
        };
    }

    /**
     * Override parent can() method for custom role-based permissions from config/roles.php
     */
    public function can($abilities, $arguments = []): bool
    {
        // Handle string permission (existing usage)
        if (is_string($abilities)) {
            $permissions = config('roles.permissions', []);
            if (isset($permissions[$abilities])) {
                $allowedRoles = $permissions[$abilities];
                return in_array($this->role, (array) $allowedRoles);
            }
            return false;
        }

        // Delegate to parent for other cases (array, Gate, etc.)
        return parent::can($abilities, $arguments);
    }

    public function roles()
    {
        return $this->morphToMany(config('permission.models.role'), 'modelable');
    }
}

