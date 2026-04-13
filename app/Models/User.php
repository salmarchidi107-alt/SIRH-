<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'tenant_id',
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
        return $this->belongsTo(Employee::class, 'employee_id');
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

    public function isEmployee(): bool
    {
        return $this->role === self::ROLE_EMPLOYEE;
    }

    public function getRoleDisplayName(): string
    {
        return match($this->role) {
            self::ROLE_SUPERADMIN => 'Super Administrateur',
            self::ROLE_ADMIN => 'Administrateur',
            self::ROLE_EMPLOYEE => 'Employé',
            default => 'Employé',
        };
    }
}

