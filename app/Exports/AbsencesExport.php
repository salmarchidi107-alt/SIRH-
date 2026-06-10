<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use App\Models\Absence;

class AbsencesExport implements FromCollection, WithHeadings, WithMapping
{
    public function __construct(
        private ?string $tenantId = null
    ) {}

    public function collection()
    {
        return Absence::with('employee')
            ->whereHas('employee')
            ->when(
                $this->tenantId,
                fn($q) => $q->where('absences.tenant_id', $this->tenantId)
            )
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function map($absence): array
    {
        return [
            $absence->employee->matricule   ?? '',
            $absence->employee->full_name   ?? '',
            $absence->employee->department  ?? '',
            $absence->type,
            $absence->start_date ? $absence->start_date->format('d/m/Y') : '',
            $absence->end_date   ? $absence->end_date->format('d/m/Y')   : '',
            $absence->days,
            $absence->status,
            $absence->reason ?? '',
            $absence->created_at ? $absence->created_at->format('d/m/Y H:i') : '',
        ];
    }

    public function headings(): array
    {
        return [
            'Matricule', 'Employé', 'Département', 'Type',
            'Date Début', 'Date Fin', 'Jours', 'Statut', 'Raison', 'Créé le',
        ];
    }
}
