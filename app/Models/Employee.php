<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;
use App\Models\Department;
use App\Models\Pointage;
use App\Traits\HasTenantScope;

class Employee extends Model
{
    use HasFactory, HasTenantScope;

    protected $fillable = [
        'tenant_id',
        'matricule',
        'first_name',
        'last_name',
        'email',
        'phone',
        'photo',
        'department',
        'department_id',
        'position',
        'sort_order',
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
        'pin',
        'plain_pin',
    ];

    protected $casts = [
        'hire_date'           => 'date',
        'birth_date'          => 'date',
        'contract_start_date' => 'date',
        'contract_end_date'   => 'date',
        'work_days'           => 'array',
        'base_salary'         => 'decimal:2',
        'work_hours_counter'  => 'decimal:2',
        'children_count'      => 'integer',
    ];

    // ─── Accessors ────────────────────────────────────────────────────────────

    public function getFullNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }

    public function getStatusLabelAttribute(): string
    {
        return \App\Enums\EmployeeStatus::tryFrom($this->status)?->label() ?? $this->status;
    }

    public function getSeniorityAttribute()
    {
        if (!$this->hire_date) return null;
        return $this->hire_date->diffInYears(now());
    }

    public function getSeniorityYearsAttribute(): int
    {
        return (int) ($this->seniority ?? 0);
    }

    public function getSeniorityRateAttribute(): float
    {
        $years = $this->seniority_years;
        if ($years < 2)  return 0.0;
        if ($years < 5)  return 0.05;
        if ($years < 10) return 0.10;
        if ($years < 15) return 0.15;
        if ($years < 20) return 0.20;
        return 0.25;
    }

    public function getSeniorityLabelAttribute(): string
    {
        $years = $this->seniority_years;
        $rate  = $this->seniority_rate * 100;
        return "{$years} ans ({$rate}%)";
    }

    public function getPhotoUrlAttribute(): string
    {
        if ($this->photo) {
            return asset('storage/' . $this->photo);
        }
        return asset(config('constants.employee.default_avatar'));
    }

    // ─── Scopes ───────────────────────────────────────────────────────────────

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', \App\Enums\EmployeeStatus::Active->value);
    }

    public function scopeStatus(Builder $query, ?string $status): Builder
    {
        return $status ? $query->where('status', $status) : $query;
    }

    public function scopeDepartment(Builder $query, ?string $department): Builder
    {
        if (!$department) return $query;

        return $query->where(function (Builder $q) use ($department) {
            $q->where('department', $department)
              ->orWhereHas('departmentRelation', fn(Builder $q2) => $q2->where('name', $department));
        });
    }

    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        if (!$term) return $query;

        $term = "%{$term}%";

        return $query->where(function (Builder $q) use ($term) {
            $q->where('first_name', 'like', $term)
              ->orWhere('last_name',  'like', $term)
              ->orWhere('matricule',  'like', $term)
              ->orWhere('email',      'like', $term)
              ->orWhere('position',   'like', $term);
        });
    }

    public function scopeDefaultOrder(Builder $query): Builder
    {
        return $query->orderBy('sort_order', 'asc')->orderBy('matricule', 'asc');
    }

    // ─── Department mutator/accessor ──────────────────────────────────────────

    public function setDepartmentAttribute($value): void
    {
        $value = trim((string) $value);
        $this->attributes['department'] = $value;

        if (!Schema::hasTable($this->getTable()) || !Schema::hasColumn($this->getTable(), 'department_id')) {
            unset($this->attributes['department_id']);
            return;
        }

        if ($value === '') {
            $this->attributes['department_id'] = null;
            return;
        }

        if (Schema::hasTable('departments')) {
            $department = Department::firstOrCreate(['name' => $value]);
            $this->attributes['department_id'] = $department->id;
        } else {
            $this->attributes['department_id'] = null;
        }
    }

    public function getDepartmentAttribute($value)
    {
        if ($this->relationLoaded('departmentRelation') && $this->departmentRelation) {
            return $this->departmentRelation->name;
        }
        return $value;
    }

    // ─── Relations ────────────────────────────────────────────────────────────

    public function departmentRelation()
    {
        return $this->belongsTo(Department::class, 'department_id');
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

    public function variableElements()
    {
        return $this->hasMany(VariableElement::class);
    }

public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Check if employee has an approved absence on a given date.
     *
     * @param \Carbon\CarbonInterface|\Carbon\Carbon|string $date
     * @return bool
     */
    public function hasApprovedAbsenceOn($date): bool
    {
        // Convert to Carbon date if needed
        $date = \Carbon\Carbon::parse($date);

        return $this->absences()
            ->where('status', 'approved')
            ->where(function ($query) use ($date) {
                $query->whereDate('start_date', '<=', $date)
                    ->whereDate('end_date', '>=', $date);
            })
            ->exists();
    }
}
