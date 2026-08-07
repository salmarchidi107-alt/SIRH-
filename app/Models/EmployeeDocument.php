<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use \App\Traits\HasTenantScope;


class EmployeeDocument extends Model
{
    use HasFactory, HasTenantScope;

    protected $fillable = [
        'employee_id',
        'name',
        'path',
        'original_name',

    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
