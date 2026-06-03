<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmployeeDocument extends Model
{
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
