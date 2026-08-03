<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\Expense;
use App\Models\Tenant;
use App\Services\ReceiptOcrService;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;

class ExpenseService
{
    // =========================================================================
    // CONTEXTE (tenant / employé courant)
    // =========================================================================

    public function currentTenantId(): ?string
    {
        $id = config('app.current_tenant_id')
            ?? (auth()->check() ? auth()->user()->tenant_id : null);

        return $id !== null ? (string) $id : null;
    }

    public function currentEmployee(): ?Employee
    {
        $user = auth()->user();
        if ($user && $user->role === 'employee' && $user->employee_id) {
            return Employee::find($user->employee_id);
        }
        return null;
    }

    private function employeesForTenant(?string $tenantId): Collection
    {
        return Employee::when($tenantId, fn ($q) => $q->where('tenant_id', $tenantId))
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get();
    }

    // =========================================================================
    // AUTORISATIONS
    // =========================================================================

    /**
     * Vérifie l'accès à une note de frais : l'employé verrouillé (mode "employé")
     * doit en être l'auteur, et le tenant doit correspondre.
     */
    public function canAccessExpense(Expense $expense): bool
    {
        $tenantId       = $this->currentTenantId();
        $lockedEmployee = $this->currentEmployee();

        if ($lockedEmployee && $expense->employee_id !== $lockedEmployee->id) {
            return false;
        }

        if ($tenantId && $expense->tenant_id !== $tenantId) {
            return false;
        }

        return true;
    }

    /**
     * Vérifie uniquement l'appartenance au tenant (utilisé pour approve/reject,
     * qui ne sont pas restreints à l'employé auteur).
     */
    public function canAccessExpenseForTenant(Expense $expense): bool
    {
        $tenantId = $this->currentTenantId();

        if ($tenantId && $expense->tenant_id !== $tenantId) {
            return false;
        }

        return true;
    }

    // =========================================================================
    // REQUÊTE FILTRÉE
    // =========================================================================

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

    public function getFilteredExpenses(Request $request): Collection
    {
        return $this->filteredExpensesQuery($request)->get();
    }

    // =========================================================================
    // INDEX
    // =========================================================================

    public function getIndexData(Request $request): array
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

        return [
            'expenses'       => $expenses,
            'statusLabels'   => Expense::STATUSES,
            'categories'     => Expense::CATEGORIES,
            'stats'          => $stats,
            'employees'      => $employee ? collect() : $this->employeesForTenant($tenantId),
            'isEmployeeMode' => (bool) $employee,
        ];
    }

    // =========================================================================
    // EXPORT PDF
    // =========================================================================

    public function buildPdfExportData(Request $request): array
    {
        $expenses = $this->filteredExpensesQuery($request)->get();

        $stats = [
            'total'   => $expenses->count(),
            'montant' => number_format((float) $expenses->sum('amount'), 2, ',', ' ') . ' MAD',
        ];

        $periodLabel = $request->filled('month') && $request->filled('year')
            ? Carbon::create()->month((int) $request->input('month'))->locale('fr')->translatedFormat('F') . ' ' . $request->input('year')
            : now()->locale('fr')->translatedFormat('F Y');

        $tenantId = $this->currentTenantId();
        $tenant   = $tenantId ? Tenant::find($tenantId) : null;

        return [
            'expenses'    => $expenses,
            'stats'       => $stats,
            'periodLabel' => $periodLabel,
            'generatedAt' => now(),
            'tenant'      => $tenant,
        ];
    }

    // =========================================================================
    // CREATE / EDIT (données de formulaire)
    // =========================================================================

    public function getCreateData(): array
    {
        $tenantId = $this->currentTenantId();
        $employee = $this->currentEmployee();

        return [
            'categories' => Expense::CATEGORIES,
            'employee'   => $employee,
            'employees'  => $employee ? collect() : $this->employeesForTenant($tenantId),
        ];
    }

    public function getEditData(Expense $expense): array
    {
        $tenantId       = $this->currentTenantId();
        $lockedEmployee = $this->currentEmployee();

        return [
            'expense'    => $expense,
            'categories' => Expense::CATEGORIES,
            'employee'   => $lockedEmployee,
            'employees'  => $lockedEmployee ? collect() : $this->employeesForTenant($tenantId),
        ];
    }

    // =========================================================================
    // STORE / UPDATE / DESTROY
    // =========================================================================

    public function createExpense(Request $request, array $validated): Expense
    {
        $tenantId       = $this->currentTenantId();
        $lockedEmployee = $this->currentEmployee();

        return Expense::create([
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
    }

    public function updateExpense(Request $request, Expense $expense, array $validated): void
    {
        $lockedEmployee = $this->currentEmployee();

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
    }

    public function approveExpense(Expense $expense): void
    {
        $expense->update(['status' => Expense::STATUS_VALIDE]);
    }

    public function rejectExpense(Expense $expense): void
    {
        $expense->update(['status' => Expense::STATUS_REJETE]);
    }

    public function destroyExpense(Expense $expense): void
    {
        if ($expense->receipt_path) {
            Storage::disk('public')->delete($expense->receipt_path);
        }

        $expense->delete();
    }

    // =========================================================================
    // OCR
    // =========================================================================

    /**
     * @return array{status: string, data?: array}
     */
    public function scanReceipt(UploadedFile $file, ?string $tenantId): array
    {
        $isPdf = $file->getClientMimeType() === 'application/pdf'
            || strtolower($file->getClientOriginalExtension()) === 'pdf';

        $result = app(ReceiptOcrService::class)->scan(
            $file->getRealPath(),
            $isPdf ? 'pdf' : $file->getClientOriginalExtension(),
            $tenantId
        );

        if (blank($result['title']) && blank($result['amount'])) {
            return ['status' => 'failed'];
        }

        return ['status' => 'ok', 'data' => $result];
    }
}
