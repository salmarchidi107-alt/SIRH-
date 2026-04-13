<?php

namespace App\Ai\Tools;

use App\Models\Absence;
use App\Models\Department;
use App\Models\Employee;
use App\Models\Salary;

class DatabaseTool
{
    public function name(): string
    {
        return 'database_search';
    }

    public function description(): string
    {
        return 'Rechercher des données RH par table ou par terme. Arguments: table (string), filter (string), fields (array). Tables autorisées: employees, absences, salaries, departments.';
    }

    public function execute(array $arguments): string
    {
        $table = trim(strtolower($arguments['table'] ?? ''));
        $filter = trim($arguments['filter'] ?? '');
        $fields = $arguments['fields'] ?? [];

        if ($table === '') {
            return $this->searchAllTables($filter);
        }

        return match ($table) {
            'employees', 'employes' => $this->searchEmployees($filter, $fields),
            'absences' => $this->searchAbsences($filter),
            'salaries' => $this->searchSalaries($filter),
            'departments' => $this->searchDepartments($filter),
            default => "Table '$table' non autorisée. Utilisez employees, absences, salaries ou departments.",
        };
    }

    protected function searchAllTables(string $filter): string
    {
        if ($filter === '') {
            return "Tables disponibles: employees, absences, salaries, departments. Exemple: table=employees, filter=nom 'Marie'.";
        }

        $result = $this->searchEmployees($filter, []);
        if (!str_contains($result, "Aucun employé trouvé")) {
            return $result;
        }

        $result = $this->searchAbsences($filter);
        if (!str_contains($result, 'Aucune absence trouvée')) {
            return $result;
        }

        $result = $this->searchSalaries($filter);
        if (!str_contains($result, 'Aucun salaire trouvé')) {
            return $result;
        }

        $result = $this->searchDepartments($filter);
        if (!str_contains($result, 'Aucun département trouvé')) {
            return $result;
        }

        return "Aucune donnée trouvée pour '$filter'.";
    }

    protected function searchEmployees(string $filter, array $fields): string
    {
        $columns = $this->employeeColumns($fields);

        $query = Employee::query();

        if ($filter !== '') {
            $term = "%{$filter}%";
            $query->where(function ($builder) use ($term) {
                $builder->where('first_name', 'like', $term)
                    ->orWhere('last_name', 'like', $term)
                    ->orWhere('matricule', 'like', $term)
                    ->orWhere('email', 'like', $term)
                    ->orWhere('phone', 'like', $term)
                    ->orWhere('position', 'like', $term)
                    ->orWhere('department', 'like', $term);
            });
        }

        $employees = $query->active()
            ->limit(10)
            ->get($columns);

        if ($employees->isEmpty()) {
            return "Aucun employé trouvé pour '$filter'";
        }

        $rows = [];
        foreach ($employees as $employee) {
            $row = [];
            foreach ($columns as $column) {
                if ($column === 'first_name') {
                    $row[] = $employee->first_name . ' ' . $employee->last_name;
                    continue;
                }
                if ($column === 'last_name') {
                    continue;
                }
                $row[] = $this->formatValue($employee->{$column});
            }
            $rows[] = $row;
        }

        $header = array_map(fn ($column) => $this->employeeLabel($column), $columns);
        if (in_array('first_name', $columns, true) && in_array('last_name', $columns, true)) {
            $header = array_map(fn ($column) => $column === 'first_name' ? 'Nom complet' : $column, $header);
        }

        return $this->formatTable($header, $rows);
    }

    protected function employeeColumns(array $fields): array
    {
        $allowed = [
            'matricule',
            'first_name',
            'last_name',
            'email',
            'phone',
            'position',
            'department',
            'base_salary',
            'status',
        ];

        if (empty($fields)) {
            return ['matricule', 'first_name', 'last_name', 'email', 'phone', 'position', 'department', 'base_salary'];
        }

        $columns = [];
        foreach ($fields as $field) {
            $field = strtolower(trim($field));
            if ($field === 'nom' || $field === 'full_name') {
                $columns[] = 'first_name';
                $columns[] = 'last_name';
                continue;
            }
            if ($field === 'salaire') {
                $field = 'base_salary';
            }
            if (in_array($field, $allowed, true)) {
                $columns[] = $field;
            }
        }

        $columns = array_values(array_unique($columns));
        return empty($columns) ? ['matricule', 'first_name', 'last_name', 'email', 'phone', 'position', 'department', 'base_salary'] : $columns;
    }

    protected function employeeLabel(string $column): string
    {
        return match ($column) {
            'matricule' => 'Matricule',
            'first_name' => 'Prénom',
            'last_name' => 'Nom',
            'email' => 'E-mail',
            'phone' => 'Téléphone',
            'position' => 'Poste',
            'department' => 'Département',
            'base_salary' => 'Salaire',
            'status' => 'Statut',
            default => ucfirst($column),
        };
    }

    protected function searchAbsences(string $filter): string
    {
        $query = Absence::with('employee');

        if ($filter !== '') {
            $term = "%{$filter}%";
            $query->where(function ($builder) use ($term) {
                $builder->where('type', 'like', $term)
                    ->orWhere('reason', 'like', $term)
                    ->orWhere('status', 'like', $term)
                    ->orWhereHas('employee', function ($builder) use ($term) {
                        $builder->where('first_name', 'like', $term)
                            ->orWhere('last_name', 'like', $term)
                            ->orWhere('matricule', 'like', $term);
                    });
            });
        }

        $absences = $query->limit(10)->get();

        if ($absences->isEmpty()) {
            return "Aucune absence trouvée pour '$filter'";
        }

        $rows = [];
        foreach ($absences as $absence) {
            $rows[] = [
                $absence->employee?->first_name . ' ' . $absence->employee?->last_name,
                $absence->type,
                $absence->start_date?->format('Y-m-d'),
                $absence->end_date?->format('Y-m-d'),
                $absence->status,
            ];
        }

        return $this->formatTable(
            ['Employé', 'Type', 'Début', 'Fin', 'Statut'],
            $rows
        );
    }

    protected function searchSalaries(string $filter): string
    {
        $query = Salary::with('employee');

        if ($filter !== '') {
            $term = "%{$filter}%";
            $query->where(function ($builder) use ($term) {
                $builder->where('status', 'like', $term)
                    ->orWhere('year', 'like', $term)
                    ->orWhere('month', 'like', $term)
                    ->orWhereHas('employee', function ($builder) use ($term) {
                        $builder->where('first_name', 'like', $term)
                            ->orWhere('last_name', 'like', $term)
                            ->orWhere('matricule', 'like', $term);
                    });
            });
        }

        $salaries = $query->limit(10)->get();

        if ($salaries->isEmpty()) {
            return "Aucun salaire trouvé pour '$filter'";
        }

        $rows = [];
        foreach ($salaries as $salary) {
            $rows[] = [
                $salary->employee?->matricule,
                $salary->employee?->first_name . ' ' . $salary->employee?->last_name,
                $salary->month,
                $salary->year,
                number_format($salary->net_salary, 2),
                $salary->status,
            ];
        }

        return $this->formatTable(
            ['Matricule', 'Employé', 'Mois', 'Année', 'Net', 'Statut'],
            $rows
        );
    }

    protected function searchDepartments(string $filter): string
    {
        $names = Department::names();

        if ($filter !== '') {
            $filter = mb_strtolower($filter, 'UTF-8');
            $names = array_filter($names, fn ($name) => str_contains(mb_strtolower($name, 'UTF-8'), $filter));
        }

        if (empty($names)) {
            return "Aucun département trouvé pour '$filter'";
        }

        $rows = array_map(fn ($name) => [$name], $names);
        return $this->formatTable(['Département'], $rows);
    }

    protected function formatTable(array $columns, array $rows): string
    {
        $header = '| ' . implode(' | ', $columns) . ' |';
        $divider = '| ' . implode(' | ', array_map(fn () => '---', $columns)) . ' |';

        $lines = [$header, $divider];
        foreach ($rows as $row) {
            $lines[] = '| ' . implode(' | ', array_map(fn ($value) => $this->formatValue($value), $row)) . ' |';
        }

        return implode("\n", $lines);
    }

    protected function formatValue(mixed $value): string
    {
        if ($value === null) {
            return '';
        }

        if (is_numeric($value)) {
            return (string) $value;
        }

        return trim((string) $value);
    }
}
