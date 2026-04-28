<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\Department;
use App\Models\Planning;
use App\Models\Room;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class PlanningService
{
    // =========================================================================
    // FILTER EMPLOYEES
    // =========================================================================

    /**
     * Filtre les employés actifs.
     *
     * Le filtre salle ne s'active que si $roomId est fourni ET $showAllRooms = false.
     * Dans weekly(), $showAllRooms = empty($roomId), donc quand une salle est
     * sélectionnée $showAllRooms = false → le filtre s'applique.
     *
     * La colonne `room` dans plannings stocke le NOM de la salle (texte).
     */
    public function filterEmployees(
        ?string $search,
        ?string $department,
        ?int    $roomId       = null,
        bool    $showAllRooms = true,
        ?Carbon $start        = null,
        ?Carbon $end          = null
    ): Collection {
        return Employee::active()
            ->when($search, fn($query) => $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name',  'like', "%{$search}%")
                  ->orWhereRaw("first_name || ' ' || last_name LIKE ?", ["%{$search}%"]);
            }))
            ->when($department, fn($query) => $query->where('department', $department))
            // ✅ S'active uniquement quand roomId est fourni ET showAllRooms = false
            ->when($roomId && !$showAllRooms, function ($query) use ($roomId, $start, $end) {
                // ✅ Résoudre le NOM depuis l'ID
                $room = Room::find($roomId);
                if (!$room) {
                    // Salle introuvable → aucun résultat
                    $query->whereRaw('1 = 0');
                    return;
                }

                // ✅ Filtrer par NOM de salle (pas par ID)
                $query->whereHas('plannings', function ($planningQuery) use ($room, $start, $end) {
                    $planningQuery->where('room', $room->name)
                        ->when($start, fn($q) => $q->whereDate('date', '>=', $start))
                        ->when($end,   fn($q) => $q->whereDate('date', '<=', $end));
                });
            })
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get();
    }

    // =========================================================================
    // GET DEPARTMENTS
    // =========================================================================

    public function getDepartments(): Collection
    {
        return Department::names();
    }

    // =========================================================================
    // GET PLANNINGS BETWEEN
    // =========================================================================

    /**
     * Retourne les plannings entre deux dates, groupés par employee_id.
     *
     * @param Carbon  $start
     * @param Carbon  $end
     * @param ?string $roomName  Nom de la salle (texte) — filtre si fourni.
     *                           Ne pas passer l'ID, la colonne stocke le nom.
     */
    public function getPlanningsBetween(
        Carbon  $start,
        Carbon  $end,
        ?string $roomName = null
    ): Collection {
        return Planning::with(['employee', 'room'])
            ->whereDate('date', '>=', $start)
            ->whereDate('date', '<=', $end)
            // ✅ Filtre par NOM de salle si fourni
            ->when($roomName, fn($q) => $q->where('room', $roomName))
            ->get()
            ->groupBy('employee_id');
    }

    // =========================================================================
    // GET EMPLOYEE PLANNING FOR RANGE
    // =========================================================================

    public function getEmployeePlanningForRange(Employee $employee, Carbon $start, Carbon $end): Collection
    {
        return Planning::where('employee_id', $employee->id)
            ->whereDate('date', '>=', $start)
            ->whereDate('date', '<=', $end)
            ->get()
            ->keyBy('date');
    }

    // =========================================================================
    // GET WEEK DAYS
    // =========================================================================

    public function getWeekDays(Carbon $startOfWeek): array
    {
        $weekDays = [];
        for ($i = 0; $i < 7; $i++) {
            $day = $startOfWeek->copy()->addDays($i);
            $weekDays[$day->format('Y-m-d')] = [
                'date'       => $day,
                'day_name'   => $day->locale('fr')->dayName,
                'day_number' => $day->day,
            ];
        }
        return $weekDays;
    }

    // =========================================================================
    // GET MONTH DAYS
    // =========================================================================

    public function getMonthDays(Carbon $startOfMonth, Carbon $endOfMonth): array
    {
        $calendarDays = [];
        for ($day = $startOfMonth->copy(); $day->lte($endOfMonth); $day->addDay()) {
            $calendarDays[] = [
                'date'           => $day->copy(),
                'date_string'    => $day->format('Y-m-d'),
                'day'            => $day->day,
                'day_name_short' => substr($day->locale('fr')->dayName, 0, 3),
                'is_weekend'     => in_array(
                    $day->dayOfWeek,
                    config('constants.planning.weekend_days'),
                    true
                ),
            ];
        }
        return $calendarDays;
    }

    // =========================================================================
    // UPDATE DRAG DROP
    // =========================================================================

    public function updateDragDrop(array $validated): void
    {
        $planning = Planning::findOrFail($validated['planning_id']);

        if (!empty($validated['duplicate'])) {
            $duplicate              = $planning->replicate();
            $duplicate->date        = $validated['new_date'];
            $duplicate->employee_id = $validated['new_employee_id'] ?? $planning->employee_id;
            $duplicate->save();
            return;
        }

        $planning->update([
            'date'        => $validated['new_date'],
            'employee_id' => $validated['new_employee_id'] ?? $planning->employee_id,
        ]);
    }
}