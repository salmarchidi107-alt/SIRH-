<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
<<<<<<< HEAD

class Department extends Model
{
    use HasFactory, \App\Traits\HasTenantScope;

    protected $fillable = [
        'tenant_id',
        'name',
        'slug', // optional
    ];

    protected $casts = [];

=======
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Collection;
use App\Models\Employee;

class Department extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
    ];

>>>>>>> 6b5799881c0e6344d7e3c861606c54fdeaa2dc06
    public function employees()
    {
        return $this->hasMany(Employee::class);
    }

<<<<<<< HEAD
    public function scopeActive($query)
    {
        return $query->whereHas('employees');
=======
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
>>>>>>> 6b5799881c0e6344d7e3c861606c54fdeaa2dc06
    }
}
