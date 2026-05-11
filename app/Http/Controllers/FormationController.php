<?php

namespace App\Http\Controllers;

use App\Models\Formation;
use App\Models\Employee;
use App\Http\Requests\FormationRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;

class FormationController extends Controller
{
    /* ══════════════════════════════════════════════════════════════════
     |  HELPERS
     ══════════════════════════════════════════════════════════════════ */

    /**
     * Détecte la colonne de tri de la table employees.
     */
    private function empSortCol(): string
    {
        static $col = null;
        if ($col) return $col;

        $cols = Schema::getColumnListing('employees');
        foreach (['prenom', 'first_name', 'nom', 'last_name', 'name'] as $try) {
            if (in_array($try, $cols)) { $col = $try; return $col; }
        }
        return $col = ($cols[0] ?? 'id');
    }

    /**
     * Détecte la relation "département" sur le modèle Employee.
     * Essaie : department, departement, service, dept.
     */
    private function empDeptRelation(): ?string
    {
        static $rel = null;
        if ($rel !== null) return $rel ?: null;

        $emp = new Employee();
        foreach (['department', 'departement', 'service', 'dept'] as $r) {
            if (method_exists($emp, $r)) {
                $rel = $r;
                return $rel;
            }
        }
        $rel = '';
        return null;
    }

    /**
     * Résout le nom de département depuis un employé.
     * Cherche d'abord la colonne directe, puis la relation.
     */
    private function resolveDeptName(Employee $emp): string
    {
        // 1. Colonne directe department_name ou dept_name sur l'employé
        foreach (['department_name','dept_name','departement_name','service_name'] as $col) {
            if (isset($emp->$col) && $emp->$col) return $emp->$col;
        }

        // 2. Via la relation chargée
        $rel = $this->empDeptRelation();
        if ($rel && $emp->relationLoaded($rel)) {
            $dept = $emp->$rel;
            if ($dept) {
                return $dept->name ?? $dept->nom ?? $dept->libelle ?? '—';
            }
        }

        // 3. Via department_id → charger manuellement depuis getDepartments
        if ($emp->department_id) {
            $depts = $this->getDepartments();
            $found = $depts->firstWhere('id', $emp->department_id);
            if ($found) {
                return $found->name ?? $found->nom ?? '—';
            }
        }

        return '—';
    }

    /**
     * Charge les employés avec la relation département si disponible.
     */
    private function employeesQuery(): \Illuminate\Database\Eloquent\Builder
    {
        $rel     = $this->empDeptRelation();
        $sortCol = $this->empSortCol();

        return $rel
            ? Employee::with($rel)->orderBy($sortCol)
            : Employee::orderBy($sortCol);
    }

    /**
     * Résout les champs "libre" (Autre).
     */
    private function resolveLibre(array $data, Request $request): array
    {
        if (empty($data['titre'])     && $request->filled('titre_libre'))
            $data['titre']     = $request->titre_libre;
        if (empty($data['formateur']) && $request->filled('formateur_libre'))
            $data['formateur'] = $request->formateur_libre;
        if (empty($data['organisme']) && $request->filled('organisme_libre'))
            $data['organisme'] = $request->organisme_libre;
        return $data;
    }

    /* ══════════════════════════════════════════════════════════════════
     |  VUE LISTE
     ══════════════════════════════════════════════════════════════════ */

    public function index(Request $request)
    {
        $rel   = $this->empDeptRelation();
        $withs = $rel ? ["employee.{$rel}"] : ['employee'];

        $query = Formation::with($withs);

        if ($request->filled('departement_id')) {
            $query->parDepartement($request->departement_id);
        }
        if ($request->filled('formation')) {
            $query->parFormation($request->formation);
        }
        if ($request->filled('statut')) {
            $query->parStatut($request->statut);
        }
        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('titre',      'like', "%$s%")
                  ->orWhere('formateur','like', "%$s%")
                  ->orWhere('organisme','like', "%$s%")
                  ->orWhereHas('employee', fn($eq) =>
                        $eq->where('nom',     'like', "%$s%")
                           ->orWhere('prenom', 'like', "%$s%")
                           ->orWhere('name',   'like', "%$s%")
                  );
            });
        }

        $formations  = $query->orderBy('date', 'desc')->paginate(15)->withQueryString();
        $departments = $this->getDepartments();
        $stats       = $this->getStats();

        // Injecte dept_name via setAttribute (persiste sur les modèles Eloquent)
        $formations->getCollection()->each(function ($f) {
            if ($f->employee) {
                $f->employee->setAttribute('dept_name', $this->resolveDeptName($f->employee));
            }
        });

        return view('lms.index', compact('formations', 'departments', 'stats'));
    }

    /* ══════════════════════════════════════════════════════════════════
     |  VUE PLANNING
     ══════════════════════════════════════════════════════════════════ */

    public function planning(Request $request)
    {
        $semaine  = $request->integer('semaine', now()->weekOfYear);
        $annee    = $request->integer('annee',   now()->year);
        $debutSem = Carbon::now()->setISODate($annee, $semaine)->startOfWeek();
        $finSem   = $debutSem->copy()->endOfWeek();

        // Formations de la semaine
        $formQuery = Formation::with('employee')
            ->parSemaine($debutSem->toDateString(), $finSem->toDateString());

        if ($request->filled('formation')) {
            $formQuery->parFormation($request->formation);
        }

        $formationsSemaine = $formQuery->get();

        // Employés
        $employeesQuery = $this->employeesQuery();

        if ($request->filled('presence')) {
            $empIds = $formationsSemaine->pluck('employee_id')->unique();
            if ($request->presence === 'present') {
                $employeesQuery->whereIn('id', $empIds);
            } else {
                $employeesQuery->whereNotIn('id', $empIds);
            }
        }

        $employees   = $employeesQuery->get();
        $departments = $this->getDepartments();

        // Injecte dept_name via setAttribute sur chaque employé
        // Utilise setAttribute pour que l'attribut soit accessible dans la vue
        $employees->each(function ($emp) use ($departments) {
            // Méthode 1 : via la relation chargée
            $rel = $this->empDeptRelation();
            if ($rel && $emp->relationLoaded($rel)) {
                $dept    = $emp->$rel;
                $deptNom = $dept ? ($dept->name ?? $dept->nom ?? $dept->libelle ?? null) : null;
                if ($deptNom) {
                    $emp->setAttribute('dept_name', $deptNom);
                    return;
                }
            }

            // Méthode 2 : via department_id dans la liste déjà chargée
            if ($emp->department_id) {
                $found = $departments->firstWhere('id', $emp->department_id);
                if ($found) {
                    $emp->setAttribute('dept_name', $found->name ?? $found->nom ?? '—');
                    return;
                }
            }

            // Fallback
            $emp->setAttribute('dept_name', '—');
        });

        $joursSemaine = [];
        for ($i = 0; $i < 7; $i++) {
            $joursSemaine[] = $debutSem->copy()->addDays($i);
        }

        return view('lms.planning', compact(
            'employees', 'formationsSemaine', 'joursSemaine',
            'semaine', 'annee', 'debutSem', 'finSem', 'departments'
        ));
    }

    /* ══════════════════════════════════════════════════════════════════
     |  AJAX : employés par département
     ══════════════════════════════════════════════════════════════════ */

    public function employeesByDepartment(Request $request)
    {
        $sortCol   = $this->empSortCol();
        $employees = Employee::where('department_id', $request->departement_id)
            ->orderBy($sortCol)
            ->get();

        return response()->json(
            $employees->map(fn($e) => [
                'id'            => $e->id,
                'nom'           => $e->nom           ?? $e->last_name  ?? '',
                'prenom'        => $e->prenom         ?? $e->first_name ?? '',
                'name'          => $e->name           ?? '',
                'department_id' => $e->department_id  ?? null,
            ])
        );
    }

    /* ══════════════════════════════════════════════════════════════════
     |  CRUD
     ══════════════════════════════════════════════════════════════════ */

    public function store(FormationRequest $request)
    {
        $data = $this->resolveLibre($request->validated(), $request);
        Formation::create($data);
        return redirect()->back()->with('success', 'Formation ajoutée avec succès.');
    }

    public function update(FormationRequest $request, Formation $formation)
    {
        $data = $this->resolveLibre($request->validated(), $request);
        $formation->update($data);
        return redirect()->back()->with('success', 'Formation mise à jour.');
    }

    public function destroy(Formation $formation)
    {
        $formation->delete();
        return redirect()->back()->with('success', 'Formation supprimée.');
    }

    /* ══════════════════════════════════════════════════════════════════
     |  EXPORT PDF
     ══════════════════════════════════════════════════════════════════ */

    public function exportPdf(Request $request)
    {
        $rel        = $this->empDeptRelation();
        $withs      = $rel ? ["employee.{$rel}"] : ['employee'];
        $formations = Formation::with($withs)->orderBy('date', 'desc')->get();
        $departments = $this->getDepartments();

        $formations->each(function ($f) use ($departments) {
            if ($f->employee) {
                $rel = $this->empDeptRelation();
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

        $pdf = Pdf::loadView('lms.pdf', compact('formations'))
            ->setPaper('a4', 'landscape');

        return $pdf->download('formations_' . now()->format('Y-m-d') . '.pdf');
    }

    /* ══════════════════════════════════════════════════════════════════
     |  UTILITAIRES PRIVÉS
     ══════════════════════════════════════════════════════════════════ */

    private function getDepartments()
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

    private function getStats(): array
    {
        return [
            'planifiees' => Formation::where('statut', 'Planifiée')->count(),
            'en_cours'   => Formation::where('statut', 'En cours')->count(),
            'terminees'  => Formation::where('statut', 'Terminée')->count(),
            'total'      => Formation::count(),
        ];
    }
}
