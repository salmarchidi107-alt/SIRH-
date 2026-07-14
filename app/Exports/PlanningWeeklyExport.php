<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use App\Models\Planning;
use Carbon\Carbon;

class PlanningWeeklyExport implements FromCollection, WithHeadings, WithMapping
{
    public function __construct(
        private ?string $tenantId = null,
        private ?int    $week     = null,
        private ?int    $year     = null,
        private array   $filters  = []
    ) {}

    public function collection()
    {
        $week = $this->week ?? now()->weekOfYear;
        $year = $this->year ?? now()->year;

        $start = now()->setISODate($year, $week)->startOfWeek(Carbon::MONDAY)->format('Y-m-d');
        $end   = now()->setISODate($year, $week)->endOfWeek(Carbon::SUNDAY)->format('Y-m-d');

        $shiftType = $this->filters['shift_type'] ?? null;
        // 'absence' et 'sans_shift' sont des pseudo-filtres calculés côté vue,
        // pas des valeurs réelles de la colonne shift_type — on les ignore ici
        // (aucune ligne de "shift" à exporter pour ces cas, voir note ci-dessous).
        $realShiftType = in_array($shiftType, ['absence', 'sans_shift']) ? null : $shiftType;

        return Planning::with('employee')
            ->when($this->tenantId, fn($q) => $q->where('tenant_id', $this->tenantId))
            ->whereBetween('date', [$start, $end])
            ->when($realShiftType, fn($q, $st) => $q->where('shift_type', $st))
            ->when(
                $this->filters['room_name'] ?? null,
                fn($q, $room) => $q->where('room', $room)
            )
            ->when(
                $this->filters['department'] ?? null,
                fn($q, $dep) => $q->whereHas('employee', function ($q2) use ($dep) {
                    $q2->where('department', $dep);
                })
            )
            ->when(
                $this->filters['search'] ?? null,
                fn($q, $search) => $q->whereHas('employee', function ($q2) use ($search) {
                    $q2->where('first_name', 'like', "%{$search}%")
                       ->orWhere('last_name', 'like', "%{$search}%");
                })
            )
            ->orderBy('date')
            ->get();
    }

    public function map($planning): array
    {
        return [
            $planning->date->format('d/m/Y'),
            $planning->employee->matricule  ?? '',
            $planning->employee->full_name  ?? '',
            $planning->employee->department ?? '',
            $planning->shift_type,
            $planning->shift_start,
            $planning->shift_end,
            $planning->room  ?? '',
            $planning->notes ?? '',
        ];
    }

    public function headings(): array
    {
        return [
            'Date', 'Matricule', 'Employé', 'Département',
            'Type Shift', 'Début', 'Fin', 'Salle', 'Notes',
        ];
    }
}
