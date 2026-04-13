<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class CountersExport implements FromArray, WithHeadings
{
    protected $countersData;
    protected $year;

    public function __construct($countersData, $year)
    {
        $this->countersData = $countersData;
        $this->year = $year;
    }

    public function array(): array
    {
        $data = [];
        foreach ($this->countersData as $row) {
            $data[] = [
                $row['employee']->matricule ?? '',
                $row['employee']->full_name,
                $row['employee']->department ?? '',
                $row['months_worked'],
                $row['acquis'],
                $row['taken'],
                $row['pending'],
                $row['solde'],
                $row['solde_if_pending'],
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
            'Mois travaillés',
            'Droits acquis',
            'Pris',
            'En attente',
            'Solde',
            'Solde si approuvé',
        ];
    }
}

