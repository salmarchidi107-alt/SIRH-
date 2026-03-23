<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class Employee extends Model
{
    use HasFactory;

    protected $fillable = [
        'matricule',
        'first_name',
        'last_name',
        'email',
        'phone',
        'photo',
        'department',
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

    public function variableElements()
    {
        return $this->hasMany(VariableElement::class);
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
}

