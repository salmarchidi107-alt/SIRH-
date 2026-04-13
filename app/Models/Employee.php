<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Notifications\Notifiable;
use App\Traits\HasTenantScope;
use App\Observers\EmployeeObserver;

class Employee extends Model
{
    use HasFactory, Notifiable, HasTenantScope;

    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'phone',
        'photo',
        'department',
        'department_id',
        'position',
        'user_id',
        'pin',
        'signature',
        'is_manager',
    ];

    protected $casts = [
        'is_manager' => 'boolean',
        'pin' => 'hashed',
    ];

    public function pointages()
    {
        return $this->hasMany(Pointage::class);
    }

    public function absences()
    {
        return $this->hasMany(Absence::class);
    }

    public function salaries()
    {
        return $this->hasMany(Salary::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function departmentRel()
    {
        return $this->belongsTo(Department::class, 'department_id');
    }

    public function variableElements()
    {
        return $this->hasMany(VariableElement::class);
    }

    /**
     * Scope active employees only
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
}

