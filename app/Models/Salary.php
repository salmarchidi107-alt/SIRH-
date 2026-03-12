<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Salary extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'month',
        'year',
        'base_salary',
        'bonuses',
        'deductions',
        'overtime_hours',
        'overtime_pay',
        'cnss_deduction',
        'amo_deduction',
        'ir_deduction',
        'net_salary',
        'paid_at',
        'notes',
    ];

    protected $casts = [
        'base_salary' => 'decimal:2',
        'bonuses' => 'decimal:2',
        'deductions' => 'decimal:2',
        'overtime_pay' => 'decimal:2',
        'cnss_deduction' => 'decimal:2',
        'amo_deduction' => 'decimal:2',
        'ir_deduction' => 'decimal:2',
        'net_salary' => 'decimal:2',
        'paid_at' => 'datetime',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function getGrossSalaryAttribute(): float
    {
        return $this->base_salary + $this->bonuses + $this->overtime_pay;
    }
}
