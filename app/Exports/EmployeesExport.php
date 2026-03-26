<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use App\Models\Employee;

class EmployeesExport implements FromCollection, WithHeadings, WithMapping
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return Employee::all();
    }

    public function map($employee): array
    {
        return [
            $employee->matricule,
            $employee->first_name,
            $employee->last_name,
            $employee->full_name,
            $employee->department,
            $employee->position,
            $employee->email,
            $employee->phone,
            $employee->status,
            $employee->hire_date ? $employee->hire_date->format('d/m/Y') : '',
            $employee->base_salary,
            $employee->contract_type,
            $employee->photo_url,
        ];
    }

    public function headings(): array
    {
        return [
            'Matricule',
            'Prénom',
            'Nom',
            'Nom Complet',
            'Département',
            'Poste',
            'Email',
            'Téléphone',
            'Statut',
            'Date Embauche',
            'Salaire Base',
            'Type Contrat',
            'Photo URL',
        ];
    }
}

