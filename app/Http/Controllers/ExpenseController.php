<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Expense;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

/**
 * Contrôleur du module "Notes de frais".
 *
 * Utilise le modèle App\Models\Expense (voir migration
 * database/migrations/2026_07_03_000000_create_expenses_table.php)
 * et votre modèle App\Models\Employee existant — aucune donnée
 * statique ou fictive : tout provient de la base.
 *
 * NOTE : adaptez App\Models\Employee (namespace, colonnes full_name /
 * department / position / tenant_id) si votre modèle d'employés réel
 * diffère.
 */
class ExpenseController extends Controller
{
    /**
     * Tenant courant, sur le même modèle que layouts/app.blade.php.
     */
    private function currentTenantId(): ?string
    {
        $id = config('app.current_tenant_id')
            ?? (auth()->check() ? auth()->user()->tenant_id : null);

        return $id !== null ? (string) $id : null;
    }

    /**
     * Employé associé à l'utilisateur connecté, uniquement si son rôle
     * est "employee" (mode lecture seule, comme dans absences.create).
     * Retourne null pour admin/rh, qui choisissent l'employé via select.
     */
    private function currentEmployee(): ?Employee
    {
        $user = auth()->user();
        if ($user && $user->role === 'employee' && $user->employee_id) {
            return Employee::find($user->employee_id);
        }
        return null;
    }

   private function employeesForTenant(?string $tenantId)
    {
        return Employee::when($tenantId, fn ($q) => $q->where('tenant_id', $tenantId))
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get();
    }

    /**
     * Liste des notes de frais.
     * - Employé connecté : uniquement ses propres notes.
     * - Admin/RH : toutes les notes du tenant, filtrables.
     */
    public function index(Request $request)
    {
        $tenantId = $this->currentTenantId();
        $employee = $this->currentEmployee();

        $query = Expense::with('employee')->forTenant($tenantId);

        if ($employee) {
            $query->forEmployee($employee->id);
        } else {
            $query->forEmployee($request->integer('employee_id') ?: null)
                  ->status($request->input('status'));
        }

        if ($request->filled('month') && $request->filled('year')) {
            $query->forMonth((int) $request->input('month'), (int) $request->input('year'));
        }

        $expenses = $query->latest('expense_date')->get();

        $stats = [
            'total'   => $expenses->count(),
            'montant' => number_format((float) $expenses->sum('amount'), 2, ',', ' ') . ' MAD',
            'soumis'  => $expenses->where('status', Expense::STATUS_SOUMIS)->count(),
            'valide'  => $expenses->where('status', Expense::STATUS_VALIDE)->count(),
            'rejete'  => $expenses->where('status', Expense::STATUS_REJETE)->count(),
        ];

        return view('expenses.index', [
            'expenses'     => $expenses,
            'statusLabels' => Expense::STATUSES,
            'stats'        => $stats,
            'employees'    => $employee ? collect() : $this->employeesForTenant($tenantId),
            'isEmployeeMode' => (bool) $employee,
        ]);
    }

    /**
     * Formulaire de création avec OCR.
     */
    public function create()
    {
        $tenantId = $this->currentTenantId();
        $employee = $this->currentEmployee();

        return view('expenses.create', [
            'categories' => Expense::CATEGORIES,
            'employee'   => $employee,
            'employees'  => $employee ? collect() : $this->employeesForTenant($tenantId),
        ]);
    }

    /**
     * Enregistrement d'une note de frais (brouillon ou soumission).
     */
    public function store(Request $request)
    {
        $tenantId = $this->currentTenantId();
        $lockedEmployee = $this->currentEmployee();

        $validated = $request->validate([
            'employee_id' => [$lockedEmployee ? 'nullable' : 'required', 'integer', 'exists:employees,id'],
            'title'       => 'required|string|max:255',
            'category'    => 'required|string|in:' . implode(',', array_keys(Expense::CATEGORIES)),
            'date'        => 'required|date',
            'amount'      => 'required|numeric|min:0',
            'currency'    => 'required|string|max:3',
            'description' => 'nullable|string',
            'receipt'     => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:10240',
        ]);

        Expense::create([
            'tenant_id'    => $tenantId,
            'employee_id'  => $lockedEmployee->id ?? $validated['employee_id'],
            'title'        => $validated['title'],
            'category'     => $validated['category'],
            'expense_date' => $validated['date'],
            'amount'       => $validated['amount'],
            'currency'     => $validated['currency'],
            'description'  => $validated['description'] ?? null,
            'receipt_path' => $request->file('receipt')?->store('receipts'),
            'status'       => $request->input('action') === 'submit'
                ? Expense::STATUS_SOUMIS
                : Expense::STATUS_BROUILLON,
        ]);

        return redirect()->route('expenses.index')->with('success', 'Note de frais enregistrée.');
    }

    /**
     * Vue d'import groupé.
     */
    public function import()
    {
        $tenantId = $this->currentTenantId();
        $employee = $this->currentEmployee();

        return view('expenses.import', [
            'employee'  => $employee,
            'employees' => $employee ? collect() : $this->employeesForTenant($tenantId),
        ]);
    }

    /**
     * Analyse OCR d'un reçu unique (appelée en AJAX depuis la vue "create").
     * Retourne les champs détectés au format JSON.
     *
     * TODO: aucun service OCR réel n'est branché ici. Intégrez un
     * fournisseur (Google Vision, AWS Textract, Tesseract…) et
     * retournez les champs réellement extraits du fichier reçu.
     * Tant que ce n'est pas fait, le JS bascule automatiquement sur
     * une simulation locale si cet endpoint échoue ou ne renvoie rien
     * d'exploitable — voir le <script> de create.blade.php.
     */
    public function ocrScan(Request $request): JsonResponse
    {
        $request->validate([
            'receipt' => 'required|file|mimes:jpg,jpeg,png,pdf|max:10240',
        ]);

        return response()->json([
            'error' => 'Service OCR non configuré côté serveur.',
        ], 501);
    }

    /**
     * Traitement de l'import groupé : analyse OCR de plusieurs reçus
     * et création des brouillons correspondants pour l'employé choisi.
     *
     * TODO: même remarque que ocrScan() — brancher un vrai service OCR.
     * Une fois fait, chaque fichier de $request->file('receipts') doit
     * être analysé puis donner lieu à un Expense::create() réel (au
     * lieu du placeholder ci-dessous qui ne persiste rien).
     */
    public function processImport(Request $request): JsonResponse
    {
        $tenantId = $this->currentTenantId();
        $lockedEmployee = $this->currentEmployee();

        $request->validate([
            'employee_id' => [$lockedEmployee ? 'nullable' : 'required', 'integer', 'exists:employees,id'],
            'receipts'    => 'required|array|min:1',
            'receipts.*'  => 'file|mimes:jpg,jpeg,png,pdf|max:10240',
        ]);

        $employeeId = $lockedEmployee->id ?? $request->integer('employee_id');

        return response()->json([
            'error' => 'Service OCR non configuré côté serveur — aucun brouillon créé.',
        ], 501);
    }
}
