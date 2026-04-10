<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use App\Models\DroitAbsence;

class DroitsAbsenceExport implements FromCollection, WithHeadings, WithMapping
{
    public function collection()
    {
        return DroitAbsence::with('employee')->get();
    }

    public function map($droit): array
    {
        return [
            $droit->employee->matricule ?? '',
            $droit->employee->full_name ?? '',
            $droit->employee->department ?? '',
            $droit->annee,
            $droit->jours_acquis,
            $droit->jours_pris,
            $droit->jours_en_attente,
            $droit->jours_solde,
            $droit->rtt_acquis,
            $droit->rtt_pris,
            $droit->rtt_solde,
        ];
    }

    public function headings(): array
    {
        return [
            'Matricule',
            'Employé',
            'Département',
            'Année',
            'Jours acquis',
            'Jours pris',
            'Jours attente',
            'Jours solde',
            'RTT acquis',
            'RTT pris',
            'RTT solde',
        ];
    }
}

