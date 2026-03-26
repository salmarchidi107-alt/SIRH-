<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use App\Models\Employee;

class TrombinoscopeExport implements FromCollection, WithHeadings, WithMapping
{
    /**
    * @return \Illuminate\Support\Collection
    */

    public function collection()
    {
        return Employee::where('status', 'active')->get();
    }

    public function map($employee): array
    {
        return [
            $employee->matricule,
            $employee->full_name,
            $employee->department,
            $employee->position,
            $employee->email,
            $employee->phone,
            $employee->photo_url,
            $employee->hire_date ? $employee->hire_date->format('d/m/Y') : '',
            $employee->base_salary,
        ];
    }

    public function headings(): array
    {
        return [
            'Matricule',
            'Nom Complet',
            'Département',
            'Poste',
            'Email',
            'Téléphone',
            'Photo URL',
            'Date Embauche',
            'Salaire Base',
        ];
    }

}
