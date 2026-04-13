<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Absence extends Model
{
    use HasFactory, \App\Traits\HasTenantScope;

    protected $fillable = [
        'tenant_id',
        'employee_id',
        'type',
        'start_date',
        'end_date',
        'days',
        'reason',
        'status',
        'approved_by',
        'approved_at',
        'replacement_id',
        'notes',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'approved_at' => 'datetime',
    ];

    const TYPES = [
        'conge_annuel' => 'Congé Annuel',
        'conge_maladie' => 'Congé Maladie',
        'conge_maternite' => 'Congé Maternité',
        'conge_paternite' => 'Congé Paternité',
        'conge_sans_solde' => 'Congé Sans Solde',
        'absence_justifiee' => 'Absence Justifiée',
        'absence_injustifiee' => 'Absence Injustifiée',
        'formation' => 'Formation',
        'mission' => 'Mission',
    ];

    const STATUSES = [
        'pending' => 'En attente',
        'approved' => 'Approuvé',
        'rejected' => 'Rejeté',
        'cancelled' => 'Annulé',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function replacement()
    {
        return $this->belongsTo(Employee::class, 'replacement_id');
    }

    public function scopeStatus(Builder $query, ?string $status): Builder
    {
        return $status ? $query->where('status', $status) : $query;
    }

    public function scopeType(Builder $query, ?string $type): Builder
    {
        return $type ? $query->where('type', $type) : $query;
    }

    public function scopeForEmployee(Builder $query, ?int $employeeId): Builder
    {
        return $employeeId ? $query->where('employee_id', $employeeId) : $query;
    }

    public function scopeSearchEmployee(Builder $query, ?string $term): Builder
    {
        if (!$term) {
            return $query;
        }

        $term = "%{$term}%";

        return $query->whereHas('employee', function (Builder $q) use ($term) {
            $q->where('first_name', 'like', $term)
              ->orWhere('last_name', 'like', $term);
        });
    }

    public function scopeDepartment(Builder $query, ?string $department): Builder
    {
        if (!$department) {
            return $query;
        }

        return $query->whereHas('employee', function (Builder $q) use ($department) {
            $q->where('department', $department);
        });
    }

    public function approver()
    {
        return $this->belongsTo(Employee::class, 'approved_by');
    }
}


