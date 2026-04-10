<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Planning extends Model
{
    use HasFactory, \App\Traits\HasTenantScope;

    protected $fillable = [
        'tenant_id',
        'employee_id',
        'date',
        'shift_start',
        'shift_end',
        'shift_type',
        'notes',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    const SHIFT_TYPES = [
        'matin' => 'Matin',
        'apres_midi' => 'Après-midi',
        'nuit' => 'Nuit',
        'journee' => 'Journée',
        'garde' => 'Garde',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}


