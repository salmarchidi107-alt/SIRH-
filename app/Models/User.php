<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Crypt;
use App\Traits\HasModulePermissions;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasModulePermissions;

    protected $fillable = [
        'name',
        'first_name',
        'last_name',
        'email',
        'password',
        'plain_password',
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

    // ─── Roles Constants ──────────────────────────────────────────────────────

    const ROLE_SUPERADMIN = 'superadmin';
    const ROLE_ADMIN      = 'admin';
    const ROLE_RH         = 'rh';
    const ROLE_EMPLOYEE   = 'employee';

    // ─── Encrypt / Decrypt plain_password ────────────────────────────────────

    public function setPlainPasswordAttribute(?string $value): void
    {
        if (!is_null($value)) {
            $this->attributes['plain_password'] = Crypt::encryptString($value);
        } else {
            $this->attributes['plain_password'] = null;
        }
    }

    public function getPlainPasswordAttribute(?string $value): ?string
    {
        if (is_null($value)) return null;

        try {
            return Crypt::decryptString($value);
        } catch (\Illuminate\Contracts\Encryption\DecryptException $e) {
            // Ancienne valeur en clair (avant migration)
            return $value;
        }
    }

    // ─── Relations ────────────────────────────────────────────────────────────

    public function tenant()
    {
        return $this->belongsTo(\App\Models\Tenant::class, 'tenant_id');
    }

    public function employee()
    {
        return $this->hasOne(Employee::class, 'user_id');
    }
    public function verificationCodes()
    {
        return $this->hasMany(\App\Models\VerificationCode::class, 'user_id');
    }

    public function activeVerificationCode()
    {
        return $this->hasOne(\App\Models\VerificationCode::class, 'user_id')
            ->where('status', \App\Models\VerificationCode::STATUS_ASSIGNED);
    }

    public function modulePermissions()
    {
        return $this->hasMany(\App\Models\UserPermission::class, 'user_id');
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

    // ─── Role Helpers ─────────────────────────────────────────────────────────

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
        return $this->role === self::ROLE_RH;
    }

    public function isAdminOrRh(): bool
    {
        return in_array($this->role, [self::ROLE_ADMIN, self::ROLE_RH]);
    }

    public function isEmployee(): bool
    {
        return $this->role === self::ROLE_EMPLOYEE;
    }

    public function isFullAccessRole(): bool
    {
        return in_array($this->role, [self::ROLE_ADMIN, self::ROLE_SUPERADMIN]);
    }

    public function getRoleDisplayName(): string
    {
        return match($this->role) {
            self::ROLE_SUPERADMIN => 'Super Administrateur',
            self::ROLE_ADMIN      => 'Administrateur',
            self::ROLE_RH         => 'Responsable RH',
            self::ROLE_EMPLOYEE   => 'Employé',
            default               => 'Employé',
        };
    }

    public function clearPermCache(): void
    {
        // Vider le cache de permissions si nécessaire
        // Laisser vide si pas de cache explicite utilisé
    }

    // ─── Permissions ──────────────────────────────────────────────────────────

    public function can($abilities, $arguments = []): bool
    {
        if (is_string($abilities)) {
            $permissions = config('roles.permissions', []);
            if (isset($permissions[$abilities])) {
                return in_array($this->role, (array) $permissions[$abilities]);
            }
            return false;
        }
        return parent::can($abilities, $arguments);
    }

    public function roles()
    {
        return $this->morphToMany(config('permission.models.role'), 'modelable');
    }
}
