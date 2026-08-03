<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Services\ExpenseService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExpenseController extends Controller
{
    public function __construct(private ExpenseService $expenseService) {}

    public function index(Request $request)
    {
        return view('expenses.index', $this->expenseService->getIndexData($request));
    }

    public function exportCsv(Request $request): StreamedResponse
    {
        $expenses = $this->expenseService->getFilteredExpenses($request);

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

    public function exportPdf(Request $request)
    {
        $data = $this->expenseService->buildPdfExportData($request);

        $pdf = Pdf::loadView('expenses.pdf', $data)->setPaper('a4', 'landscape');

        $filename = 'notes-de-frais-' . now()->format('Y-m-d_His') . '.pdf';

        return $pdf->download($filename);
    }

    public function create()
    {
        return view('expenses.create', $this->expenseService->getCreateData());
    }

    public function store(Request $request)
    {
        $lockedEmployee = $this->expenseService->currentEmployee();

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

        $this->expenseService->createExpense($request, $validated);

        return redirect()->route('expenses.index')->with('success', 'Note de frais enregistrée et validée.');
    }

    public function edit(Expense $expense)
    {
        if (! $this->expenseService->canAccessExpense($expense)) {
            abort(403);
        }

        return view('expenses.edit', $this->expenseService->getEditData($expense));
    }

    public function update(Request $request, Expense $expense)
    {
        if (! $this->expenseService->canAccessExpense($expense)) {
            abort(403);
        }

        $lockedEmployee = $this->expenseService->currentEmployee();

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

        $this->expenseService->updateExpense($request, $expense, $validated);

        return redirect()->route('expenses.index')->with('success', 'Note de frais mise à jour.');
    }

    public function approve(Expense $expense)
    {
        if (! $this->expenseService->canAccessExpenseForTenant($expense)) {
            abort(403);
        }

        $this->expenseService->approveExpense($expense);

        return redirect()->route('expenses.index')->with('success', 'Note de frais validée.');
    }


    public function reject(Expense $expense)
    {
        if (! $this->expenseService->canAccessExpenseForTenant($expense)) {
            abort(403);
        }

        $this->expenseService->rejectExpense($expense);

        return redirect()->route('expenses.index')->with('success', 'Note de frais rejetée.');
    }

    public function destroy(Expense $expense)
    {
        if (! $this->expenseService->canAccessExpense($expense)) {
            abort(403);
        }

        $this->expenseService->destroyExpense($expense);

        return redirect()->route('expenses.index')->with('success', 'Note de frais supprimée.');
    }

    public function ocrScan(Request $request): JsonResponse
    {
        $request->validate([
            'receipt' => 'required|file|mimes:jpg,jpeg,png,pdf|max:10240',
        ]);

        try {
            $result = $this->expenseService->scanReceipt(
                $request->file('receipt'),
                $this->expenseService->currentTenantId()
            );

            if ($result['status'] === 'failed') {
                return response()->json([
                    'error' => "OCR n'a pas réussi à lire le reçu — saisissez les champs manuellement.",
                ], 422);
            }

            return response()->json($result['data']);
        } catch (\Throwable $e) {
            report($e);

            return response()->json([
                'error' => "Erreur lors de l'analyse OCR (" . $e->getMessage() . ")",
            ], 500);
        }
    }
}
