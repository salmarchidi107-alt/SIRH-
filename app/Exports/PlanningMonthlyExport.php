<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use App\Models\Planning;

class PlanningMonthlyExport implements FromCollection, WithHeadings, WithMapping
{
    public function __construct(
        private ?string $tenantId = null,
        private ?int    $month    = null,
        private ?int    $year     = null,
        private array   $filters  = []
    ) {}

    public function collection()
    {
        $month = $this->month ?? now()->month;
        $year  = $this->year  ?? now()->year;

        $shiftType     = $this->filters['shift_type'] ?? null;
        $realShiftType = in_array($shiftType, ['absence', 'sans_shift']) ? null : $shiftType;

        return Planning::with('employee')
            ->when($this->tenantId, fn($q) => $q->where('tenant_id', $this->tenantId))
            ->whereMonth('date', $month)
            ->whereYear('date',  $year)
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
