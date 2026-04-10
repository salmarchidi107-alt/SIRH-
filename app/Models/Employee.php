<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class Employee extends Model
{
    use HasFactory, \App\Traits\HasTenantScope;

    protected $fillable = [
        'matricule',
        'tenant_id',
        'department_id',
        'first_name',
        'last_name',
        'email',
        'phone',
        'photo',
        'position',
        'diploma_type',
        'skills',
        'contract_type',
        'hire_date',
        'birth_date',
        'status',
        'base_salary',
        'manager_id',
        'cnss',
        'cin',
        'address',
        'family_situation',
        'children_count',
        'payment_method',
        'bank',
        'rib',
        'contractual_benefits',
        'emergency_contact',
        'emergency_phone',
        'work_hours',
        'contract_start_date',
        'contract_end_date',
        'work_days',
        'cp_days',
        'work_hours_counter',
        'user_id',
        'is_manager',
    ];

    protected $casts = [
        'hire_date' => 'date',
        'birth_date' => 'date',
        'contract_start_date' => 'date',
        'contract_end_date' => 'date',
        'work_days' => 'array',
        'base_salary' => 'decimal:2',
        'work_hours_counter' => 'decimal:2',
        'children_count' => 'integer',
    ];

    public function getFullNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }

    public function getSeniorityAttribute()
    {
        if (!$this->hire_date) return null;
        return $this->hire_date->diffInYears(now());
    }

    public function manager()
    {
        return $this->belongsTo(Employee::class, 'manager_id');
    }

    public function subordinates()
    {
        return $this->hasMany(Employee::class, 'manager_id');
    }

    public function absences()
    {
        return $this->hasMany(Absence::class);
    }

    public function plannings()
    {
        return $this->hasMany(Planning::class);
    }

    public function salaries()
    {
        return $this->hasMany(Salary::class);
    }

    public function pointages()
    {
        return $this->hasMany(Pointage::class);
    }

    public function getPhotoUrlAttribute(): string
    {
        if ($this->photo) {
            return Storage::url($this->photo);
        }
        return asset('images/default-avatar.png');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeSearch($query, string $search)
    {
        return $query->where(function($q) use ($search) {
            $q->where('first_name', 'like', "%{$search}%")
              ->orWhere('last_name', 'like', "%{$search}%")
              ->orWhere('matricule', 'like', "%{$search}%")
              ->orWhere('email', 'like', "%{$search}%");
        });
    }

    public function scopeByDepartment($query, $department)
    {
        if (!$department) {
            return $query;
        }
        if (is_numeric($department)) {
            return $query->where('department_id', $department);
        }
        return $query->whereHas('department', fn($q) => $q->where('name', $department));
    }

    public function scopeByStatus($query, ?string $status)
    {
        return $status ? $query->where('status', $status) : $query;
    }

    public function scopeExcluding($query, int $excludeId)
    {
        return $query->where('id', '!=', $excludeId);
    }
}

