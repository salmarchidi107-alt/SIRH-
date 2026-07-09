<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Expense;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

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

    public function exportCsv(Request $request): \Symfony\Component\HttpFoundation\StreamedResponse
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

        $filename = 'notes-de-frais-' . now()->format('Y-m-d_His') . '.csv';

        return response()->streamDownload(function () use ($expenses) {
            $handle = fopen('php://output', 'w');
            fwrite($handle, "\xEF\xBB\xBF");
            fputcsv($handle, ['Employé', 'Titre', 'Catégorie', 'Date', 'Montant', 'Devise', 'Statut'], ';');

            foreach ($expenses as $expense) {
                fputcsv($handle, [
                    $expense->employee->full_name ?? '—',
                    $expense->title,
                    $expense->category_label,
                    optional($expense->expense_date)->format('d/m/Y'),
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
            'receipt_path' => $request->file('receipt')?->store('receipts', 'public'),
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
            'amount'      => 'required|numeric|min:0',
            'currency'    => 'required|string|max:3',
            'description' => 'nullable|string',
            'receipt'     => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:10240',
        ]);

        $expense->fill([
            'employee_id'  => $lockedEmployee->id ?? $validated['employee_id'],
            'title'        => $validated['title'],
            'category'     => $validated['category'],
            'expense_date' => $validated['date'],
            'amount'       => $validated['amount'],
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

    public function import()
    {
        $tenantId = $this->currentTenantId();
        $employee = $this->currentEmployee();

        return view('expenses.import', [
            'employee'  => $employee,
            'employees' => $employee ? collect() : $this->employeesForTenant($tenantId),
        ]);
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

    public function processImport(Request $request): JsonResponse
    {
        $tenantId = $this->currentTenantId();
        $lockedEmployee = $this->currentEmployee();

        $request->validate([
            'employee_id' => [$lockedEmployee ? 'nullable' : 'required', 'integer', 'exists:employees,id'],
            'receipts'    => 'required|array|min:1',
            'receipts.*'  => 'file|mimes:jpg,jpeg,png,pdf|max:10240',
        ]);

        $defaultEmployeeId = $lockedEmployee->id ?? $request->integer('employee_id');
        $ocrService = app(\App\Services\ReceiptOcrService::class);

        $created = [];
        $failed = [];

        foreach ($request->file('receipts') as $file) {
            try {
                $isPdf = $file->getClientMimeType() === 'application/pdf'
                    || strtolower($file->getClientOriginalExtension()) === 'pdf';

                $result = $ocrService->scan(
                    $file->getRealPath(),
                    $isPdf ? 'pdf' : $file->getClientOriginalExtension(),
                    $tenantId
                );

                $expense = Expense::create([
                    'tenant_id'    => $tenantId,
                    'employee_id'  => $result['employee_id'] ?? $defaultEmployeeId,
                    'title'        => $result['title'] ?? $file->getClientOriginalName(),
                    'category'     => $result['category'] ?? 'autre',
                    'expense_date' => $result['date'] ?? now()->toDateString(),
                    'amount'       => $result['amount'] ?? 0,
                    'currency'     => 'MAD',
                    'receipt_path' => $file->store('receipts', 'public'),
                    'status'       => Expense::STATUS_VALIDE,
                ]);

                $created[] = [
                    'title'  => $expense->title,
                    'amount' => (string) $expense->amount,
                    'source' => $file->getClientOriginalName(),
                ];
            } catch (\Throwable $e) {
                report($e);
                $failed[] = $file->getClientOriginalName();
            }
        }

        return response()->json([
            'created' => $created,
            'failed'  => $failed,
        ]);
    }
}
