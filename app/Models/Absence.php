<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
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

    public function approver()
    {
        return $this->belongsTo(Employee::class, 'approved_by');
    }
}


