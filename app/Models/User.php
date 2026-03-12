<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
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

    // Roles constants
    const ROLE_ADMIN = 'admin';
    const ROLE_RH = 'rh';
    const ROLE_EMPLOYEE = 'employee';

    /**
     * Check if user is admin
     */
    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    /**
     * Check if user is RH
     */
    public function isRh(): bool
    {
        return $this->role === self::ROLE_RH;
    }

    /**
     * Check if user is employee
     */
    public function isEmployee(): bool
    {
        return $this->role === self::ROLE_EMPLOYEE;
    }

    /**
     * Get the employee associated with this user
     */
    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    /**
     * Get role display name
     */
    public function getRoleDisplayName(): string
    {
        return match($this->role) {
            self::ROLE_ADMIN => 'Administrateur',
            self::ROLE_RH => 'Responsable RH',
            self::ROLE_EMPLOYEE => 'Employé',
            default => 'Employé',
        };
    }
}
