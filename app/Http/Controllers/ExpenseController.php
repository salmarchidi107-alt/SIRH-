<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Expense;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Barryvdh\DomPDF\Facade\Pdf;

class ExpenseController extends Controller
{
    private function currentTenantId(): ?string
    {
        $id = config('app.current_tenant_id')
            ?? (auth()->check() ? auth()->user()->tenant_id : null);

        return $id !== null ? (string) $id : null;
    }

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
     * Construit la requête filtrée commune à index(), exportCsv() et exportPdf(),
     * pour ne jamais avoir à dupliquer la logique de filtrage à trois endroits.
     */
    private function filteredExpensesQuery(Request $request)
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

        // Nouveau filtre : catégorie
        if ($request->filled('category')) {
            $query->where('category', $request->input('category'));
        }

        // Nouveau filtre : recherche texte libre dans la description
        if ($request->filled('description')) {
            $query->where('description', 'like', '%' . $request->input('description') . '%');
        }

        return $query->latest('expense_date');
    }

    public function index(Request $request)
    {
        $tenantId = $this->currentTenantId();
        $employee = $this->currentEmployee();

        $expenses = $this->filteredExpensesQuery($request)->get();

        $stats = [
            'total'   => $expenses->count(),
            'montant' => number_format((float) $expenses->sum('amount'), 2, ',', ' ') . ' MAD',
            'valide'  => $expenses->where('status', Expense::STATUS_VALIDE)->count(),
            'rejete'  => $expenses->where('status', Expense::STATUS_REJETE)->count(),
        ];

        return view('expenses.index', [
            'expenses'       => $expenses,
            'statusLabels'   => Expense::STATUSES,
            'categories'     => Expense::CATEGORIES,
            'stats'          => $stats,
            'employees'      => $employee ? collect() : $this->employeesForTenant($tenantId),
            'isEmployeeMode' => (bool) $employee,
        ]);
    }

    public function exportCsv(Request $request): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $expenses = $this->filteredExpensesQuery($request)->get();

        $filename = 'notes-de-frais-' . now()->format('Y-m-d_His') . '.csv';

        return response()->streamDownload(function () use ($expenses) {
            $handle = fopen('php://output', 'w');
            fwrite($handle, "\xEF\xBB\xBF");
            fputcsv($handle, ['Employé', 'Titre', 'Catégorie', 'Description', 'Date', 'HT', 'TVA', 'TTC', 'Devise', 'Statut'], ';');

            foreach ($expenses as $expense) {
                fputcsv($handle, [
                    $expense->employee->full_name ?? '—',
                    $expense->title,
                    $expense->category_label,
                    $expense->description,
                    optional($expense->expense_date)->format('d/m/Y'),
                    $expense->amount_excluding_tax !== null ? number_format((float) $expense->amount_excluding_tax, 2, ',', '') : '',
                    $expense->vat_amount !== null ? number_format((float) $expense->vat_amount, 2, ',', '') : '',
                    number_format((float) $expense->amount, 2, ',', ''),
                    $expense->currency,
                    $expense->status_label,
                ], ';');
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    /**
     * Export PDF des notes de frais filtrées, avec les mêmes données que l'export
     * Excel (HT / TVA / TTC / description incluses), au format imprimable.
     *
     * Nécessite le paquet barryvdh/laravel-dompdf :
     *   composer require barryvdh/laravel-dompdf
     */
    public function exportPdf(Request $request)
    {
        $expenses = $this->filteredExpensesQuery($request)->get();

        $stats = [
            'total'   => $expenses->count(),
            'montant' => number_format((float) $expenses->sum('amount'), 2, ',', ' ') . ' MAD',
        ];

        $periodLabel = $request->filled('month') && $request->filled('year')
            ? \Illuminate\Support\Carbon::create()->month((int) $request->input('month'))->locale('fr')->translatedFormat('F') . ' ' . $request->input('year')
            : now()->locale('fr')->translatedFormat('F Y');

        $tenantId = $this->currentTenantId();
        $tenant = $tenantId ? \App\Models\Tenant::find($tenantId) : null;

        $pdf = Pdf::loadView('expenses.pdf', [
            'expenses'    => $expenses,
            'stats'       => $stats,
            'periodLabel' => $periodLabel,
            'generatedAt' => now(),
            'tenant'      => $tenant,
        ])->setPaper('a4', 'landscape');

        $filename = 'notes-de-frais-' . now()->format('Y-m-d_His') . '.pdf';

        return $pdf->download($filename);
    }

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

    public function store(Request $request)
    {
        $tenantId = $this->currentTenantId();
        $lockedEmployee = $this->currentEmployee();

        $validated = $request->validate([
            'employee_id' => [$lockedEmployee ? 'nullable' : 'required', 'integer', 'exists:employees,id'],
            'title'       => 'required|string|max:255',
            'category'    => 'required|string|in:' . implode(',', array_keys(Expense::CATEGORIES)),
            'date'        => 'required|date',
            'amount'                => 'required|numeric|min:0',
            'amount_excluding_tax'  => 'nullable|numeric|min:0',
            'vat_amount'            => 'nullable|numeric|min:0',
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
            'amount'                => $validated['amount'],
            'amount_excluding_tax'  => $validated['amount_excluding_tax'] ?? null,
            'vat_amount'            => $validated['vat_amount'] ?? null,
            'currency'     => $validated['currency'],
            'description'  => $validated['description'] ?? null,
            'receipt_path' => $request->filled('receipt_path')
                ? $request->input('receipt_path')
                : $request->file('receipt')?->store('receipts', 'public'),
            'status'       => Expense::STATUS_VALIDE,
        ]);

        return redirect()->route('expenses.index')->with('success', 'Note de frais enregistrée et validée.');
    }

    public function edit(Expense $expense)
    {
        $tenantId = $this->currentTenantId();
        $lockedEmployee = $this->currentEmployee();

        if ($lockedEmployee && $expense->employee_id !== $lockedEmployee->id) {
            abort(403);
        }
        if ($tenantId && $expense->tenant_id !== $tenantId) {
            abort(403);
        }

        return view('expenses.edit', [
            'expense'    => $expense,
            'categories' => Expense::CATEGORIES,
            'employee'   => $lockedEmployee,
            'employees'  => $lockedEmployee ? collect() : $this->employeesForTenant($tenantId),
        ]);
    }

    public function update(Request $request, Expense $expense)
    {
        $tenantId = $this->currentTenantId();
        $lockedEmployee = $this->currentEmployee();

        if ($lockedEmployee && $expense->employee_id !== $lockedEmployee->id) {
            abort(403);
        }
        if ($tenantId && $expense->tenant_id !== $tenantId) {
            abort(403);
        }

        $validated = $request->validate([
            'employee_id' => [$lockedEmployee ? 'nullable' : 'required', 'integer', 'exists:employees,id'],
            'title'       => 'required|string|max:255',
            'category'    => 'required|string|in:' . implode(',', array_keys(Expense::CATEGORIES)),
            'date'        => 'required|date',
            'amount'                => 'required|numeric|min:0',
            'amount_excluding_tax'  => 'nullable|numeric|min:0',
            'vat_amount'            => 'nullable|numeric|min:0',
            'currency'    => 'required|string|max:3',
            'description' => 'nullable|string',
            'receipt'     => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:10240',
        ]);

        $expense->fill([
            'employee_id'  => $lockedEmployee->id ?? $validated['employee_id'],
            'title'        => $validated['title'],
            'category'     => $validated['category'],
            'expense_date' => $validated['date'],
            'amount'                => $validated['amount'],
            'amount_excluding_tax'  => $validated['amount_excluding_tax'] ?? null,
            'vat_amount'            => $validated['vat_amount'] ?? null,
            'currency'     => $validated['currency'],
            'description'  => $validated['description'] ?? null,
        ]);

        if ($request->hasFile('receipt')) {
            $expense->receipt_path = $request->file('receipt')->store('receipts', 'public');
        }

        $expense->save();

        return redirect()->route('expenses.index')->with('success', 'Note de frais mise à jour.');
    }

    /**
     * Marque une note de frais comme Validée.
     */
    public function approve(Expense $expense)
    {
        $tenantId = $this->currentTenantId();
        if ($tenantId && $expense->tenant_id !== $tenantId) {
            abort(403);
        }

        $expense->update(['status' => Expense::STATUS_VALIDE]);

        return redirect()->route('expenses.index')->with('success', 'Note de frais validée.');
    }

    /**
     * Marque une note de frais comme Rejetée.
     */
    public function reject(Expense $expense)
    {
        $tenantId = $this->currentTenantId();
        if ($tenantId && $expense->tenant_id !== $tenantId) {
            abort(403);
        }

        $expense->update(['status' => Expense::STATUS_REJETE]);

        return redirect()->route('expenses.index')->with('success', 'Note de frais rejetée.');
    }

    public function ocrScan(Request $request): JsonResponse
    {
        $request->validate([
            'receipt' => 'required|file|mimes:jpg,jpeg,png,pdf|max:10240',
        ]);

        try {
            $file = $request->file('receipt');
            $isPdf = $file->getClientMimeType() === 'application/pdf'
                || strtolower($file->getClientOriginalExtension()) === 'pdf';

            $result = app(\App\Services\ReceiptOcrService::class)->scan(
                $file->getRealPath(),
                $isPdf ? 'pdf' : $file->getClientOriginalExtension(),
                $this->currentTenantId()
            );

            if (blank($result['title']) && blank($result['amount'])) {
                return response()->json([
                    'error' => "OCR n'a pas réussi à lire le reçu — saisissez les champs manuellement.",
                ], 422);
            }

            return response()->json($result);
        } catch (\Throwable $e) {
            report($e);

            return response()->json([
                'error' => "Erreur lors de l'analyse OCR (" . $e->getMessage() . ")",
            ], 500);
        }
    }

    // NOTE DE SUPPRESSION : les méthodes import() et processImport() ont été
    // retirées ici — la fonctionnalité "Import groupé" est supprimée du projet
    // conformément à la demande. Pensez à retirer les routes correspondantes
    // dans routes/web.php (voir instructions fournies séparément) et à supprimer
    // le fichier resources/views/expenses/import.blade.php.
}
