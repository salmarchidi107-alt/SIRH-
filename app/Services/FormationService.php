<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\Formation;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;

class FormationService
{

    public function empSortCol(): string
    {
        static $col = null;
        if ($col) return $col;
        $cols = Schema::getColumnListing('employees');
        foreach (['prenom', 'first_name', 'nom', 'last_name', 'name'] as $try) {
            if (in_array($try, $cols)) { $col = $try; return $col; }
        }
        return $col = ($cols[0] ?? 'id');
    }

    public function empSearchCols(): array
    {
        static $searchCols = null;
        if ($searchCols !== null) return $searchCols;
        $available  = Schema::getColumnListing('employees');
        $candidates = ['first_name', 'last_name', 'nom', 'prenom', 'name'];
        $searchCols = array_values(array_filter(
            $candidates,
            fn($c) => in_array($c, $available, true)
        ));
        return $searchCols;
    }

    public function empDeptRelation(): ?string
    {
        static $rel = null;
        if ($rel !== null) return $rel ?: null;
        $emp = new Employee();
        foreach (['department', 'departement', 'service', 'dept'] as $r) {
            if (method_exists($emp, $r)) { $rel = $r; return $rel; }
        }
        $rel = '';
        return null;
    }

    public function resolveDeptName(Employee $emp): string
    {
        foreach (['department_name','dept_name','departement_name','service_name'] as $col) {
            if (isset($emp->$col) && $emp->$col) return $emp->$col;
        }
        $rel = $this->empDeptRelation();
        if ($rel && $emp->relationLoaded($rel)) {
            $dept = $emp->$rel;
            if ($dept) return $dept->name ?? $dept->nom ?? $dept->libelle ?? '—';
        }
        if ($emp->department_id) {
            $depts = $this->getDepartments();
            $found = $depts->firstWhere('id', $emp->department_id);
            if ($found) return $found->name ?? $found->nom ?? '—';
        }
        return '—';
    }

    public function employeesQuery(): Builder
    {
        $rel     = $this->empDeptRelation();
        $sortCol = $this->empSortCol();
        return $rel
            ? Employee::with($rel)->orderBy($sortCol)
            : Employee::orderBy($sortCol);
    }

    public function resolveLibre(array $data, Request $request): array
    {
        if (empty($data['titre'])     && $request->filled('titre_libre'))
            $data['titre']     = $request->titre_libre;
        if (empty($data['formateur']) && $request->filled('formateur_libre'))
            $data['formateur'] = $request->formateur_libre;
        if (empty($data['organisme']) && $request->filled('organisme_libre'))
            $data['organisme'] = $request->organisme_libre;
        return $data;
    }

    public function getIndexData(Request $request): array
    {
        $rel   = $this->empDeptRelation();
        $withs = $rel ? ["employee.{$rel}"] : ['employee'];

        $query = Formation::with($withs);

        // Employé : ne voit que ses propres formations
        $authUser = auth()->user();
        if ($authUser->isEmployee()) {
            $employee = Employee::where('user_id', $authUser->id)->first();
            if ($employee) {
                $query->where('employee_id', $employee->id);
            }
        } else {
            // Admin/RH : filtres complets
            if ($request->filled('departement_id')) {
                $query->parDepartement($request->departement_id);
            }
        }

        if ($request->filled('formation')) {
            $query->parFormation($request->formation);
        }
        if ($request->filled('statut')) {
            $query->parStatut($request->statut);
        }
        if ($request->filled('search')) {
            $s          = $request->search;
            $searchCols = $this->empSearchCols();

            $query->where(function ($q) use ($s, $searchCols) {
                $q->where('titre',       'like', "%$s%")
                  ->orWhere('formateur', 'like', "%$s%")
                  ->orWhere('organisme', 'like', "%$s%");

                if (!empty($searchCols)) {
                    $q->orWhereHas('employee', function ($eq) use ($s, $searchCols) {
                        $eq->where(function ($sub) use ($s, $searchCols) {
                            foreach ($searchCols as $col) {
                                $sub->orWhere($col, 'like', "%$s%");
                            }
                        });
                    });
                }
            });
        }

        $formations  = $query->orderBy('date', 'desc')->paginate(100)->withQueryString();
        $departments = $this->getDepartments();
        $stats       = $this->getStats();

        $formations->getCollection()->each(function ($f) {
            if ($f->employee) {
                $f->employee->setAttribute('dept_name', $this->resolveDeptName($f->employee));
            }
        });

        return compact('formations', 'departments', 'stats');
    }


    public function getPlanningData(Request $request): array
    {
        $semaine  = $request->integer('semaine', now()->weekOfYear);
        $annee    = $request->integer('annee',   now()->year);
        $debutSem = Carbon::now()->setISODate($annee, $semaine)->startOfWeek();
        $finSem   = $debutSem->copy()->endOfWeek();

        $formQuery = Formation::with('employee')
            ->parSemaine($debutSem->toDateString(), $finSem->toDateString());

        if ($request->filled('formation')) {
            $formQuery->parFormation($request->formation);
        }

        $formationsSemaine = $formQuery->get();

        // Filtre selon le rôle
        $authUser = auth()->user();

        if ($authUser->isEmployee()) {
            // L'employé ne voit que lui-même
            $employeesQuery = Employee::where('user_id', $authUser->id);
        } else {
            $employeesQuery = $this->employeesQuery();

            if ($request->filled('presence')) {
                $empIds = $formationsSemaine->pluck('employee_id')->unique();
                if ($request->presence === 'present') {
                    $employeesQuery->whereIn('id', $empIds);
                } else {
                    $employeesQuery->whereNotIn('id', $empIds);
                }
            }
        }

        $employees   = $employeesQuery->get();
        $departments = $this->getDepartments();

        $employees->each(function ($emp) use ($departments) {
            $rel = $this->empDeptRelation();
            if ($rel && $emp->relationLoaded($rel)) {
                $dept    = $emp->$rel;
                $deptNom = $dept ? ($dept->name ?? $dept->nom ?? $dept->libelle ?? null) : null;
                if ($deptNom) { $emp->setAttribute('dept_name', $deptNom); return; }
            }
            if ($emp->department_id) {
                $found = $departments->firstWhere('id', $emp->department_id);
                if ($found) { $emp->setAttribute('dept_name', $found->name ?? $found->nom ?? '—'); return; }
            }
            $emp->setAttribute('dept_name', '—');
        });

        $joursSemaine = [];
        for ($i = 0; $i < 7; $i++) {
            $joursSemaine[] = $debutSem->copy()->addDays($i);
        }

        return compact(
            'employees', 'formationsSemaine', 'joursSemaine',
            'semaine', 'annee', 'debutSem', 'finSem', 'departments'
        );
    }


    public function getEmployeesByDepartment(?int $departementId): Collection
    {
        $sortCol   = $this->empSortCol();
        $employees = Employee::where('department_id', $departementId)
            ->orderBy($sortCol)
            ->get();

        return $employees->map(fn($e) => [
            'id'            => $e->id,
            'nom'           => $e->nom           ?? $e->last_name  ?? '',
            'prenom'        => $e->prenom         ?? $e->first_name ?? '',
            'name'          => $e->name           ?? '',
            'department_id' => $e->department_id  ?? null,
        ]);
    }


    public function createFormation(array $data): Formation
    {
        return Formation::create($data);
    }

    public function updateFormation(Formation $formation, array $data): Formation
    {
        $formation->update($data);

        return $formation;
    }

    public function deleteFormation(Formation $formation): void
    {
        $formation->delete();
    }


    public function getExportPdfData(): array
    {
        $rel         = $this->empDeptRelation();
        $withs       = $rel ? ["employee.{$rel}"] : ['employee'];
        $formations  = Formation::with($withs)->orderBy('date', 'desc')->get();
        $departments = $this->getDepartments();

        $formations->each(function ($f) use ($departments) {
            if ($f->employee) {
                $rel     = $this->empDeptRelation();
                $deptNom = null;
                if ($rel && $f->employee->relationLoaded($rel)) {
                    $dept    = $f->employee->$rel;
                    $deptNom = $dept ? ($dept->name ?? $dept->nom ?? null) : null;
                }
                if (!$deptNom && $f->employee->department_id) {
                    $found   = $departments->firstWhere('id', $f->employee->department_id);
                    $deptNom = $found ? ($found->name ?? $found->nom ?? null) : null;
                }
                $f->employee->setAttribute('dept_name', $deptNom ?? '—');
            }
        });

        return compact('formations');
    }

    public function getDepartments(): Collection
    {
        foreach (['Department', 'Departement', 'Service'] as $model) {
            $class = "\\App\\Models\\{$model}";
            if (class_exists($class)) {
                try {
                    $col = Schema::hasColumn((new $class)->getTable(), 'name') ? 'name' : 'nom';
                    return $class::orderBy($col)->get();
                } catch (\Exception $e) {
                    continue;
                }
            }
        }
        return collect();
    }

    public function getStats(): array
    {
        return [
            'planifiees' => Formation::where('statut', 'Planifiée')->count(),
            'en_cours'   => Formation::where('statut', 'En cours')->count(),
            'terminees'  => Formation::where('statut', 'Terminée')->count(),
            'total'      => Formation::count(),
        ];
    }
}
