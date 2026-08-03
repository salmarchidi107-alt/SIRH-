<?php

namespace App\Services\Absence;

use App\Models\Absence;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;


class AbsenceConflictService
{
    public function findOtherEmployeeApprovedOverlap(int $employeeId, string $startDate, string $endDate): ?Absence
    {
        return Absence::with('employee')
            ->whereHas('employee')
            ->where('employee_id', '!=', $employeeId)
            ->where('status', 'approved')
            ->where(fn ($q) => $this->applyOverlapConditions($q, $startDate, $endDate))
            ->first();
    }


    public function hasSameEmployeeApprovedOverlap(int $employeeId, string $startDate, string $endDate): bool
    {
        return Absence::whereHas('employee')
            ->where('employee_id', $employeeId)
            ->where('status', 'approved')
            ->where(fn ($q) => $this->applyOverlapConditions($q, $startDate, $endDate))
            ->exists();
    }

    private function applyOverlapConditions(Builder $query, string $startDate, string $endDate): Builder
    {
        return $query->whereBetween('start_date', [$startDate, $endDate])
            ->orWhereBetween('end_date', [$startDate, $endDate])
            ->orWhere(function ($q2) use ($startDate, $endDate) {
                $q2->where('start_date', '<=', $startDate)
                   ->where('end_date', '>=', $endDate);
            });
    }

    public function buildSelfConflictsData(array $employeeIds): array
    {
        return Absence::whereIn('employee_id', $employeeIds)
            ->where('status', 'approved')
            ->select(['id', 'employee_id', 'type', 'start_date', 'end_date'])
            ->get()
            ->map(fn ($a) => [
                'employee_id' => $a->employee_id,
                'type_label'  => Absence::TYPES[$a->type] ?? $a->type,
                'start_date'  => $a->start_date->format('Y-m-d'),
                'end_date'    => $a->end_date->format('Y-m-d'),
                'start_fmt'   => $a->start_date->format('d/m/Y'),
                'end_fmt'     => $a->end_date->format('d/m/Y'),
            ])
            ->toArray();
    }

    public function buildConflicts(Collection $absences): Collection
    {
        $approved  = $absences->where('status', 'approved')->values();
        $conflicts = collect();
        $seen      = [];

        foreach ($approved as $i => $a1) {
            foreach ($approved as $j => $a2) {
                if ($i >= $j) continue;
                if ($a1->employee_id === $a2->employee_id) continue;

                $dept1 = $a1->employee->department ?? '';
                $dept2 = $a2->employee->department ?? '';
                if ($dept1 !== $dept2 || empty($dept1)) continue;

                $start1 = Carbon::parse($a1->start_date);
                $end1   = Carbon::parse($a1->end_date);
                $start2 = Carbon::parse($a2->start_date);
                $end2   = Carbon::parse($a2->end_date);

                if ($start1->gt($end2) || $start2->gt($end1)) continue;

                $key = min($a1->id, $a2->id) . '-' . max($a1->id, $a2->id);
                if (in_array($key, $seen)) continue;
                $seen[] = $key;

                $overlapStart = $start1->gt($start2) ? $start1 : $start2;
                $overlapEnd   = $end1->lt($end2)     ? $end1   : $end2;

                $conflicts->push([
                    'employee_id_1' => $a1->employee_id,
                    'employee_id_2' => $a2->employee_id,
                    'employee_id'   => $a1->employee_id,
                    'employee'      => ($a1->employee->full_name ?? '?') . ' ↔ ' . ($a2->employee->full_name ?? '?'),
                    'employee1'     => $a1->employee->full_name ?? '?',
                    'employee2'     => $a2->employee->full_name ?? '?',
                    'absence1'      => Absence::TYPES[$a1->type] ?? $a1->type,
                    'absence2'      => Absence::TYPES[$a2->type] ?? $a2->type,
                    'start'         => $overlapStart->format('d/m'),
                    'end'           => $overlapEnd->format('d/m/Y'),
                    'department'    => $dept1,
                    'a'             => $a1,
                    'b'             => $a2,
                ]);
            }
        }

        return $conflicts;
    }

    public function getConflictsForMonth(Request $request): Collection
    {
        $month        = $request->get('month', now()->month);
        $year         = $request->get('year', now()->year);
        $startOfMonth = Carbon::createFromDate($year, $month, 1)->startOfMonth();
        $endOfMonth   = $startOfMonth->copy()->endOfMonth();

        $absences = Absence::with(['employee'])
            ->whereHas('employee')
            ->where('status', 'approved')
            ->where(function ($q) use ($startOfMonth, $endOfMonth) {
                $q->whereBetween('start_date', [$startOfMonth, $endOfMonth])
                  ->orWhereBetween('end_date', [$startOfMonth, $endOfMonth])
                  ->orWhere(fn ($q2) => $q2->where('start_date', '<=', $startOfMonth)
                                          ->where('end_date', '>=', $endOfMonth));
            })
            ->when($request->employee_id, fn ($q) => $q->where('employee_id', $request->employee_id))
            ->get();

        return $this->buildConflicts($absences);
    }


    public function buildAbsenceMap(Collection $absences, int $month, int $year): array
    {
        $absenceMap = [];

        foreach ($absences as $absence) {
            $empId = $absence->employee_id;
            if (! isset($absenceMap[$empId])) {
                $absenceMap[$empId] = [];
            }

            $start = Carbon::parse($absence->start_date);
            $end   = Carbon::parse($absence->end_date);

            for ($d = $start->copy(); $d->lte($end); $d->addDay()) {
                if ($d->month == $month && $d->year == $year) {
                    $absenceMap[$empId][$d->day] = $absence;
                }
            }
        }

        return $absenceMap;
    }
}
