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
        private ?int    $year     = null
    ) {}

    public function collection()
    {
        $week = $this->week ?? now()->weekOfYear;
        $year = $this->year ?? now()->year;

        $start = now()->setISODate($year, $week)->startOfWeek(Carbon::MONDAY)->format('Y-m-d');
        $end   = now()->setISODate($year, $week)->endOfWeek(Carbon::SUNDAY)->format('Y-m-d');

        return Planning::with('employee')
            ->when($this->tenantId, fn($q) => $q->where('tenant_id', $this->tenantId))
            ->whereBetween('date', [$start, $end])
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
