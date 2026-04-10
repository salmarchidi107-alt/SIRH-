<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'employee_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    
    const ROLE_ADMIN = 'admin';
    const ROLE_RH = 'rh';
    const ROLE_EMPLOYEE = 'employee';

    
    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    
    public function isRh(): bool
    {
        return $this->role === self::ROLE_RH;
    }

  
    public function isEmployee(): bool
    {
        return $this->role === self::ROLE_EMPLOYEE;
    }

   
    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    public function getEmployeeByLegacyKeyAttribute()
    {
        return Employee::where('user_id', $this->id)->first();
    }

   
    public function getRoleDisplayName(): string
    {
        return \App\Enums\UserRole::tryFrom($this->role)?->label() ?? 'Employé';
    }

    public function isAdminOrRh(): bool
    {
        return in_array($this->role, [\App\Enums\UserRole::Admin->value, \App\Enums\UserRole::Rh->value]);
    }

    public function can($abilities, $arguments = []): bool
    {
        if (is_string($abilities)) {
            $permissions = config('roles.permissions', []);
            $allowedRoles = $permissions[$abilities] ?? [];
            return in_array($this->role, $allowedRoles);
        }
        return parent::can($abilities, $arguments);
    }
}
