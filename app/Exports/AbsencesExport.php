<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use App\Models\Absence;
use App\Models\Absence as AbsenceModel;

class AbsencesExport implements FromCollection, WithHeadings, WithMapping
{
    public function collection()
    {
        return Absence::with('employee')->get();
    }

    public function map($absence): array
    {
        return [
            $absence->employee->matricule ?? '',
            $absence->employee->full_name ?? '',
            $absence->employee->department ?? '',
            $absence->type,
            $absence->start_date ? $absence->start_date->format('d/m/Y') : '',
            $absence->end_date ? $absence->end_date->format('d/m/Y') : '',
            $absence->days,
            $absence->status,
            $absence->reason ?? '',
            $absence->created_at ? $absence->created_at->format('d/m/Y H:i') : '',
        ];
    }

    public function headings(): array
    {
        return [
            'Matricule',
            'Employé',
            'Département',
            'Type',
            'Date Début',
            'Date Fin',
            'Jours',
            'Statut',
            'Raison',
            'Créé le',
        ];
    }
}

