<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\Department;
use App\Models\Planning;
use App\Models\Room;
use App\Models\Tenant;
use App\Exports\PlanningMonthlyExport;
use App\Exports\PlanningWeeklyExport;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class PlanningService
{
    // =========================================================================
    // INDEX
    // =========================================================================

    /**
     * Construit toutes les données nécessaires à la vue planning.index.
     */
    public function getIndexData(Request $request): array
    {
        $month      = $request->month ?? now()->month;
        $year       = $request->year ?? now()->year;
        $search     = $request->search;
        $department = $request->department;
        $shift_type = $request->shift_type;

        [$startOfMonth, $endOfMonth] = $this->buildMonthRange($month, $year);

        $employees = Employee::query()
            ->when($search, function ($q) use ($search) {
                $q->where(function ($query) use ($search) {
                    $query->where('first_name', 'like', "%{$search}%")
                          ->orWhere('last_name', 'like', "%{$search}%");
                });
            })
            ->when($department, fn($q) => $q->where('department', $department))
            ->orderBy('last_name')
            ->get();

        $plannings   = $this->getPlanningsBetween($startOfMonth, $endOfMonth);
        $departments = $this->getDepartments();

        return compact('employees', 'plannings', 'departments', 'month', 'year', 'search', 'department', 'shift_type');
    }

    // =========================================================================
    // WEEKLY
    // =========================================================================

    /**
     * Construit toutes les données nécessaires à la vue planning.weekly.
     */
    public function getWeeklyData(Request $request, $authUser): array
    {
        $rooms      = Room::all();
        $week       = (int) ($request->week ?? now()->weekOfYear);
        $year       = (int) ($request->year ?? now()->year);
        $search     = $request->search;
        $department = $request->department;
        $shift_type = $request->shift_type;
        $roomId     = $request->room_id;

        $showAllRooms = empty($roomId);

        [$startOfWeek, $endOfWeek] = $this->buildWeekRange($week, $year);
        $roomName = $this->resolveRoomName($roomId);

        $isEmployee = ($authUser->role === 'employee');

        if ($isEmployee && $authUser->employee) {
            $employees    = Employee::where('id', $authUser->employee->id)->get();
            $search       = null;
            $department   = null;
            $roomId       = null;
            $showAllRooms = true;
        } else {
            $employees = $this->filterEmployees($search, $department, $roomId, $showAllRooms, $startOfWeek, $endOfWeek);
        }

        $plannings   = $this->getPlanningsBetween($startOfWeek, $endOfWeek, $roomName);
        $departments = $this->getDepartments();
        $weekDays    = $this->getWeekDays($startOfWeek);

        return compact(
            'employees', 'plannings', 'weekDays', 'week', 'year',
            'startOfWeek', 'endOfWeek', 'search', 'department',
            'departments', 'rooms', 'showAllRooms', 'shift_type', 'isEmployee'
        );
    }

    // =========================================================================
    // MONTHLY
    // =========================================================================

    /**
     * Construit toutes les données nécessaires à la vue planning.monthly.
     */
    public function getMonthlyData(Request $request, $authUser): array
    {
        $month      = $request->month      ?? now()->month;
        $year       = $request->year       ?? now()->year;
        $search     = $request->search;
        $department = $request->department;
        $shift_type = $request->shift_type;
        $roomId     = $request->room_id;

        [$startOfMonth, $endOfMonth] = $this->buildMonthRange($month, $year);
        $roomName     = $this->resolveRoomName($roomId);
        $showAllRooms = empty($roomId);

        $isEmployee = ($authUser->role === 'employee');

        if ($isEmployee && $authUser->employee) {
            $employees    = Employee::where('id', $authUser->employee->id)->get();
            $search       = null;
            $department   = null;
            $roomId       = null;
            $showAllRooms = true;
        } else {
            $employees = $this->filterEmployees($search, $department, $roomId, $showAllRooms, $startOfMonth, $endOfMonth);
        }

        $plannings   = $this->getPlanningsBetween($startOfMonth, $endOfMonth, $roomName);
        $departments = $this->getDepartments();
        $rooms       = Room::all();
        $daysOfMonth = $this->buildDaysCollection($startOfMonth, $endOfMonth);

        return compact(
            'employees', 'plannings', 'daysOfMonth',
            'month', 'year', 'startOfMonth', 'endOfMonth',
            'search', 'department', 'departments',
            'rooms', 'shift_type', 'roomId', 'isEmployee'
        );
    }

    // =========================================================================
    // CRUD SHIFT (store / update / destroy)
    // =========================================================================

    public function createPlanning(array $validated): Planning
    {
        return Planning::create($validated);
    }

    public function updatePlanning(Planning $planning, array $validated): Planning
    {
        $planning->update($validated);
        return $planning;
    }

    public function deletePlanning(Planning $planning): void
    {
        $planning->delete();
    }

    // =========================================================================
    // DRAG & DROP
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

    // =========================================================================
    // UPDATE ROOM
    // =========================================================================

    /**
     * Met à jour la salle affectée à un employé sur une période donnée.
     * Retourne le nom de la salle résolue (utile pour la réponse JSON du contrôleur).
     */
    public function updateEmployeeRoom(array $validated): ?string
    {
        $roomName = $this->resolveRoomName($validated['room_id'] ?? null);

        Planning::where('employee_id', $validated['employee_id'])
            ->whereDate('date', '>=', $validated['start'])
            ->whereDate('date', '<=', $validated['end'])
            ->update(['room' => $roomName]);

        return $roomName;
    }

    // =========================================================================
    // EXPORT PDF - PREPARATION DES DONNEES
    // =========================================================================

    /**
     * Prépare les données pour l'export PDF hebdomadaire.
     * Le contrôleur se charge de l'appel à Pdf::loadView()->download().
     */
    public function getWeeklyPdfData(Request $request): array
    {
        $week       = $request->week ?? now()->weekOfYear;
        $year       = $request->year ?? now()->year;
        $search     = $request->search;
        $department = $request->department;
        $shift_type = $request->shift_type;
        $roomId     = $request->room_id;

        [$startOfWeek, $endOfWeek] = $this->buildWeekRange($week, $year);
        $roomName     = $this->resolveRoomName($roomId);
        $showAllRooms = empty($roomId);

        $employees = $this->filterEmployees($search, $department, $roomId, $showAllRooms, $startOfWeek, $endOfWeek);
        $plannings = $this->getPlanningsBetween($startOfWeek, $endOfWeek, $roomName);

        // Filtrage shift_type — logique identique à celle de la vue weekly.blade.php
        [$employees, $plannings] = $this->applyWeeklyShiftTypeFilter($employees, $plannings, $shift_type, $startOfWeek, $endOfWeek);

        $weekDays = $this->getWeekDays($startOfWeek);
        $tenant   = $this->resolveCurrentTenant();
        $filename = "planning_week_{$week}_{$year}.pdf";

        return [
            'view'     => 'planning.weekly_pdf',
            'filename' => $filename,
            'data'     => [
                'employees'   => $employees,
                'plannings'   => $plannings,
                'weekDays'    => $weekDays,
                'week'        => $week,
                'year'        => $year,
                'startOfWeek' => $startOfWeek,
                'endOfWeek'   => $endOfWeek,
                'search'      => $search,
                'department'  => $department,
                'shift_type'  => $shift_type,
                'roomName'    => $roomName,
                'tenant'      => $tenant,
            ],
        ];
    }

    /**
     * Prépare les données pour l'export PDF mensuel.
     */
    public function getMonthlyPdfData(Request $request): array
    {
        $month      = $request->month      ?? now()->month;
        $year       = $request->year       ?? now()->year;
        $search     = $request->search;
        $department = $request->department;
        $shift_type = $request->shift_type;
        $roomId     = $request->room_id;

        [$startOfMonth, $endOfMonth] = $this->buildMonthRange($month, $year);
        $roomName     = $this->resolveRoomName($roomId);
        $showAllRooms = empty($roomId);

        $employees = $this->filterEmployees($search, $department, $roomId, $showAllRooms, $startOfMonth, $endOfMonth);
        $plannings = $this->getPlanningsBetween($startOfMonth, $endOfMonth, $roomName);

        // La vue monthly ne gère pas absence/sans_shift, seulement un filtre shift_type simple
        $plannings = $this->applySimpleShiftTypeFilter($plannings, $shift_type);

        $departments = $this->getDepartments();
        $daysOfMonth = $this->buildDaysCollection($startOfMonth, $endOfMonth);
        $tenant      = $this->resolveCurrentTenant();
        $filename    = "planning_mensuel_{$month}_{$year}.pdf";

        return [
            'view'     => 'planning.monthly_pdf',
            'filename' => $filename,
            'data'     => compact(
                'employees', 'plannings', 'daysOfMonth',
                'month', 'year', 'startOfMonth', 'endOfMonth',
                'search', 'department', 'departments', 'shift_type', 'tenant'
            ),
        ];
    }

    // =========================================================================
    // EXPORT EXCEL - PREPARATION DES EXPORTS
    // =========================================================================

    /**
     * Prépare l'export Excel mensuel (instance Export + nom de fichier).
     * Le contrôleur se charge de l'appel à Excel::download().
     */
    public function getMonthlyExcelExport(Request $request, int $tenantId): array
    {
        $month    = (int) ($request->month ?? now()->month);
        $year     = (int) ($request->year  ?? now()->year);
        $filename = "planning_mensuel_{$month}_{$year}.xlsx";

        $filters = [
            'department' => $request->department,
            'search'     => $request->search,
            'shift_type' => $request->shift_type,
            'room_name'  => $this->resolveRoomName($request->room_id),
        ];

        return [
            'export'   => new PlanningMonthlyExport($tenantId, $month, $year, $filters),
            'filename' => $filename,
        ];
    }

    /**
     * Prépare l'export Excel hebdomadaire (instance Export + nom de fichier).
     */
    public function getWeeklyExcelExport(Request $request, int $tenantId): array
    {
        $week     = (int) ($request->week ?? now()->weekOfYear);
        $year     = (int) ($request->year ?? now()->year);
        $filename = "planning_semaine_{$week}_{$year}.xlsx";

        $filters = [
            'department' => $request->department,
            'search'     => $request->search,
            'shift_type' => $request->shift_type,
            'room_name'  => $this->resolveRoomName($request->room_id),
        ];

        return [
            'export'   => new PlanningWeeklyExport($tenantId, $week, $year, $filters),
            'filename' => $filename,
        ];
    }

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
            // S'active uniquement quand roomId est fourni ET showAllRooms = false
            ->when($roomId && !$showAllRooms, function ($query) use ($roomId, $start, $end) {
                $room = Room::find($roomId);
                if (!$room) {
                    $query->whereRaw('1 = 0');
                    return;
                }
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

    /**
     * Retourne la liste des départements.
     *
     * Utilise la table departments si elle existe et contient des données,
     * sinon retombe sur les départements distincts dans la table employees.
     */
    public function getDepartments(): Collection
    {
        // Essayer d'abord la table departments
        try {
            $departments = Department::orderBy('name')->pluck('name');
            if ($departments->isNotEmpty()) {
                return $departments;
            }
        } catch (\Exception $e) {
            // Table departments inexistante ou vide → fallback
        }

        // Fallback : départements distincts depuis les employés
        return Employee::active()
            ->whereNotNull('department')
            ->where('department', '!=', '')
            ->distinct()
            ->orderBy('department')
            ->pluck('department');
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
    public function getPlanningsBetween(Carbon $start, Carbon $end, ?string $roomName = null): Collection
    {
        $tenantId = auth()->user()?->tenant_id;

        return Planning::with(['employee', 'room'])
            ->when($tenantId, function ($q) use ($tenantId) {
                $q->where(function ($query) use ($tenantId) {
                    $query->where('tenant_id', $tenantId)
                          ->orWhereNull('tenant_id');
                });
            })
            ->whereDate('date', '>=', $start)
            ->whereDate('date', '<=', $end)
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
    // HELPERS PRIVES — construction de plages de dates
    // =========================================================================

    private function buildMonthRange($month, $year): array
    {
        $startOfMonth = Carbon::create($year, $month, 1)->startOfMonth();
        $endOfMonth   = $startOfMonth->copy()->endOfMonth();

        return [$startOfMonth, $endOfMonth];
    }

    private function buildWeekRange($week, $year): array
    {
        $startOfWeek = now()->setISODate($year, $week)->startOfWeek(Carbon::MONDAY);
        $endOfWeek   = $startOfWeek->copy()->endOfWeek(Carbon::SUNDAY);

        return [$startOfWeek, $endOfWeek];
    }

    private function buildDaysCollection(Carbon $start, Carbon $end): Collection
    {
        $days    = collect();
        $current = $start->copy();

        while ($current <= $end) {
            $days->push($current->copy());
            $current->addDay();
        }

        return $days;
    }

    // =========================================================================
    // HELPERS PRIVES — salle / tenant
    // =========================================================================

    private function resolveRoomName(?int $roomId): ?string
    {
        if (!$roomId) {
            return null;
        }

        return Room::find($roomId)?->name;
    }

    private function resolveCurrentTenant(): ?Tenant
    {
        return auth()->user()?->tenant
            ?? Tenant::find(config('app.current_tenant_id'));
    }

    // =========================================================================
    // HELPERS PRIVES — filtrage shift_type (réutilisés par les exports PDF)
    // =========================================================================

    /**
     * Filtre absence / sans_shift / shift_type simple, utilisé par l'export PDF hebdomadaire.
     */
    private function applyWeeklyShiftTypeFilter(Collection $employees, Collection $plannings, ?string $shiftType, Carbon $start, Carbon $end): array
    {
        if ($shiftType === 'absence') {
            $employees = $employees->filter(function ($emp) use ($start, $end) {
                for ($d = $start->copy(); $d->lte($end); $d->addDay()) {
                    if ($emp->hasApprovedAbsenceOn($d)) {
                        return true;
                    }
                }
                return false;
            })->values();
        } elseif ($shiftType === 'sans_shift') {
            $employees = $employees->filter(function ($emp) use ($plannings, $start, $end) {
                $empPlannings = $plannings->get($emp->id, collect());
                for ($d = $start->copy(); $d->lte($end); $d->addDay()) {
                    $hasShift = $empPlannings->filter(fn($p) =>
                        Carbon::parse($p->date)->format('Y-m-d') === $d->format('Y-m-d')
                    )->isNotEmpty();
                    if ($hasShift) {
                        return false;
                    }
                }
                return true;
            })->values();
        } elseif ($shiftType) {
            $plannings = $this->applySimpleShiftTypeFilter($plannings, $shiftType);
        }

        return [$employees, $plannings];
    }

    /**
     * Filtre simple par shift_type (utilisé par la vue monthly et en fallback weekly).
     */
    private function applySimpleShiftTypeFilter(Collection $plannings, ?string $shiftType): Collection
    {
        if (!$shiftType) {
            return $plannings;
        }

        return $plannings
            ->map(fn($items) => $items->where('shift_type', $shiftType))
            ->filter(fn($items) => $items->isNotEmpty());
    }
}
