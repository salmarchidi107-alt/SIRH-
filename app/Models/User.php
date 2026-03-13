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
        return $this->belongsTo(Employee::class);
    }

   
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
