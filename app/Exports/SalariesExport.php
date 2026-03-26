<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use App\Models\Salary;

class SalariesExport implements FromCollection, WithHeadings, WithMapping
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return Salary::with('employee')->get();
    }

    public function map($salary): array
    {
        return [
            $salary->month_name . ' ' . $salary->year,
            $salary->employee->matricule,
            $salary->employee->full_name,
            $salary->employee->department,
            $salary->gross_salary,
            $salary->cnss_deduction + $salary->amo_deduction,
            $salary->ir_deduction,
            $salary->net_salary,
            $salary->status_label,
        ];
    }

    public function headings(): array
    {
        return [
            'Période',
            'Matricule',
            'Employé',
            'Département',
            'Brut',
            'CNSS+AMO',
            'IR',
            'Net',
            'Statut',
        ];
    }
}

