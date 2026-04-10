<?php

namespace App\Ai\Tools;

use App\Models\Employee;

class EmployeeTool
{
    public function name(): string
    {
        return 'employee_search';
    }

    public function description(): string
    {
        return 'Rechercher employés par nom, matricule, département, salaire, téléphone ou email. Arguments: query (string), fields (array).';
    }

    public function execute(array $arguments): string
    {
        $query = trim($arguments['query'] ?? '');
        $fields = $arguments['fields'] ?? [];

        if ($query === '') {
            return 'Précisez un terme de recherche pour l\'employé.';
        }

        $columns = $this->resolveColumns($fields);

        $employees = Employee::where(function ($builder) use ($query) {
                $term = '%' . $query . '%';
                $builder->where('first_name', 'like', $term)
                    ->orWhere('last_name', 'like', $term)
                    ->orWhere('matricule', 'like', $term)
                    ->orWhere('email', 'like', $term)
                    ->orWhere('phone', 'like', $term)
                    ->orWhere('position', 'like', $term)
                    ->orWhere('department', 'like', $term);
            })
            ->active()
            ->limit(10)
            ->get($columns);

        if ($employees->isEmpty()) {
            return "Aucun employé trouvé pour '$query'";
        }

        $header = array_map(fn ($column) => $this->columnLabel($column), $columns);
        $rows = [];

        foreach ($employees as $employee) {
            $row = [];
            foreach ($columns as $column) {
                if ($column === 'first_name') {
                    $row[] = trim($employee->first_name . ' ' . $employee->last_name);
                    continue;
                }

                if ($column === 'last_name') {
                    continue;
                }

                $value = $employee->{$column};
                if ($column === 'base_salary') {
                    $value = $value ? number_format($value, 2, ',', ' ') : '';
                }
                $row[] = $value;
            }
            $rows[] = $row;
        }

        return $this->formatTable($header, $rows);
    }

    protected function resolveColumns(array $fields): array
    {
        $default = ['matricule', 'first_name', 'last_name', 'email', 'phone', 'position', 'department', 'base_salary'];
        $allowed = ['matricule', 'first_name', 'last_name', 'email', 'phone', 'position', 'department', 'base_salary', 'status'];

        if (empty($fields)) {
            return $default;
        }

        $resolved = [];
        foreach ($fields as $field) {
            $field = strtolower(trim((string) $field));

            if ($field === 'nom' || $field === 'full_name') {
                $resolved[] = 'first_name';
                $resolved[] = 'last_name';
                continue;
            }

            if ($field === 'salaire') {
                $field = 'base_salary';
            }

            if (in_array($field, $allowed, true)) {
                $resolved[] = $field;
            }
        }

        $resolved = array_values(array_unique($resolved));

        if (in_array('first_name', $resolved, true) && in_array('last_name', $resolved, true)) {
            $resolved = array_filter($resolved, fn ($column) => $column !== 'last_name');
            $resolved = array_values($resolved);
        }

        return empty($resolved) ? $default : $resolved;
    }

    protected function columnLabel(string $column): string
    {
        return match ($column) {
            'matricule' => 'Matricule',
            'first_name' => 'Nom complet',
            'last_name' => 'Nom',
            'email' => 'Email',
            'phone' => 'Téléphone',
            'position' => 'Poste',
            'department' => 'Département',
            'base_salary' => 'Salaire',
            'status' => 'Statut',
            default => ucfirst($column),
        };
    }

    protected function formatTable(array $columns, array $rows): string
    {
        $header = '| ' . implode(' | ', $columns) . ' |';
        $divider = '| ' . implode(' | ', array_fill(0, count($columns), '---')) . ' |';

        $lines = [$header, $divider];
        foreach ($rows as $row) {
            $lines[] = '| ' . implode(' | ', array_map(fn ($cell) => $cell === null ? '' : (string) $cell, $row)) . ' |';
        }

        return implode("\n", $lines);
    }
}
?>

