<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class DroitsAbsenceExport implements FromArray, WithHeadings
{
    // Reçoit les mêmes données que la page Compteurs
    // (calculées par AbsenceController::buildCountersData)

    public function __construct(private array $countersData) {}

    public function array(): array
    {
        $data = [];
        foreach ($this->countersData as $row) {
            $data[] = [
                $row['employee']->matricule    ?? '',
                $row['employee']->full_name    ?? '',
                $row['employee']->department   ?? '',
                (int) $row['months_worked'],
                $this->fmt($row['acquis']),
                $this->fmt($row['taken']),
                $this->fmt($row['pending']),
                $this->fmt($row['solde']),
                $this->fmt($row['solde_if_pending']),
            ];
        }
        return $data;
    }

    public function headings(): array
    {
        return [
            'Matricule',
            'Employé',
            'Département',
            'Mois travaillés (depuis embauche)',
            'Droits acquis',
            'Pris',
            'En attente',
            'Solde',
            'Solde si approuvé',
        ];
    }

    private function fmt($value): string
    {
        return str_replace('.', ',', number_format((float) $value, 1, '.', ''));
    }
}
