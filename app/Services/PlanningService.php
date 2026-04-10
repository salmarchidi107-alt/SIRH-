<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\Department;
use App\Models\Planning;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class PlanningService
{
    public function filterEmployees(?string $search, ?string $department): Collection
    {
        return Employee::active()
            ->when($search, fn ($query) => $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhereRaw("first_name || ' ' || last_name LIKE ?", ["%{$search}%"]);
            }))
            ->when($department, fn ($query) => $query->where('department', $department))
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get();
    }

    public function getDepartments(): Collection
    {
        return Department::names();
    }

    public function getPlanningsBetween(Carbon $start, Carbon $end): Collection
    {
        return Planning::with('employee')
            ->whereDate('date', '>=', $start)
            ->whereDate('date', '<=', $end)
            ->get()
            ->groupBy('employee_id');
    }

    public function getEmployeePlanningForRange(Employee $employee, Carbon $start, Carbon $end): Collection
    {
        return Planning::where('employee_id', $employee->id)
            ->whereDate('date', '>=', $start)
            ->whereDate('date', '<=', $end)
            ->get()
            ->keyBy('date');
    }

    public function getWeekDays(Carbon $startOfWeek): array
    {
        $weekDays = [];
        for ($i = 0; $i < 7; $i++) {
            $day = $startOfWeek->copy()->addDays($i);
            $weekDays[$day->format('Y-m-d')] = [
                'date' => $day,
                'day_name' => $day->locale('fr')->dayName,
                'day_number' => $day->day,
            ];
        }

        return $weekDays;
    }

    public function getMonthDays(Carbon $startOfMonth, Carbon $endOfMonth): array
    {
        $calendarDays = [];
        for ($day = $startOfMonth->copy(); $day->lte($endOfMonth); $day->addDay()) {
            $calendarDays[] = [
                'date' => $day->copy(),
                'date_string' => $day->format('Y-m-d'),
                'day' => $day->day,
                'day_name_short' => substr($day->locale('fr')->dayName, 0, 3),
                'is_weekend' => in_array($day->dayOfWeek, config('constants.planning.weekend_days'), true),
            ];
        }

        return $calendarDays;
    }

    public function updateDragDrop(array $validated): void
    {
        $planning = Planning::findOrFail($validated['planning_id']);

        if (!empty($validated['duplicate'])) {
            $duplicate = $planning->replicate();
            $duplicate->date = $validated['new_date'];
            $duplicate->employee_id = $validated['new_employee_id'] ?? $planning->employee_id;
            $duplicate->save();

            return;
        }

        $planning->update([
            'date' => $validated['new_date'],
            'employee_id' => $validated['new_employee_id'] ?? $planning->employee_id,
        ]);
    }
}
