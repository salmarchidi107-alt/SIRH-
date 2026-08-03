<?php

namespace App\Services\Absence;

use App\Models\Department;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class EmployeeFilterService
{
    public function applyFilters(Builder $query, Request $request): Builder
    {
        $query->when($request->search, fn ($q) => $q->where(function ($q) use ($request) {
                $q->where('first_name', 'like', "%{$request->search}%")
                  ->orWhere('last_name', 'like', "%{$request->search}%")
                  ->orWhereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ["%{$request->search}%"])
                  ->orWhere('matricule', 'like', "%{$request->search}%");
            }))
            ->when($request->department, fn ($q, $dep) => $q->where('department', $dep));

        return $query;
    }

    public function getDepartments()
    {
        return Department::names();
    }


    public function buildEmployeeOptions(Collection $employees): Collection
    {
        return $employees->map(fn ($emp) => [
            'id'         => $emp->id,
            'label'      => $emp->full_name . ' — ' . $emp->department,
            'department' => $emp->department,
        ])->values();
    }
}
