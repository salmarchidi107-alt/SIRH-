<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use App\Models\Planning;

class PlanningWeeklyExport implements FromCollection, WithHeadings, WithMapping
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return Planning::with('employee')->whereBetween('date', [now()->startOfWeek(), now()->endOfWeek()])->orderBy('date')->get();
    }

    public function map($planning): array
    {
        return [
            $planning->date->format('d/m/Y'),
            $planning->employee->matricule,
            $planning->employee->full_name,
            $planning->employee->department,
            $planning->shift_type,
            $planning->shift_start,
            $planning->shift_end,
            $planning->notes ?? '',
        ];
    }

    public function headings(): array
    {
        return [
            'Date',
            'Matricule',
            'Employé',
            'Département',
            'Type Shift',
            'Début',
            'Fin',
            'Notes',
        ];
    }
}

