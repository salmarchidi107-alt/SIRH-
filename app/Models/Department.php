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

    public static function names(): Collection
    {
        if (Schema::hasTable('departments')) {
            return self::orderBy('name')->pluck('name');
        }

        return Employee::whereNotNull('department')
            ->distinct()
            ->pluck('department')
            ->filter()
            ->sort()
            ->values();
    }

    public static function counts(): Collection
    {
        if (Schema::hasTable('departments')) {
            return self::withCount('employees')
                ->orderBy('name')
                ->pluck('employees_count', 'name');
        }

        return Employee::groupBy('department')
            ->selectRaw('department, count(*) as total')
            ->pluck('total', 'department');
    }
}
