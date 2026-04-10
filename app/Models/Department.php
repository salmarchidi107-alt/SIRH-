<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    use HasFactory, \App\Traits\HasTenantScope;

    protected $fillable = [
        'tenant_id',
        'name',
        'slug', // optional
    ];

    protected $casts = [];

    public function employees()
    {
        return $this->hasMany(Employee::class);
    }

    public function scopeActive($query)
    {
        return $query->whereHas('employees');
    }
}
