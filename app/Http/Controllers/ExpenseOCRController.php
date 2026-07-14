<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Exceptions\OCR\OcrException;
use App\Http\Requests\ScanReceiptRequest;
use App\Services\ExpenseOCRService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Contrôleur dédié au scan OCR des justificatifs de dépense.
 *
 * NOTE DE MIGRATION : ce fichier remplace app/Http/Controllers/Api/ExpenseOCRController.php.
 * Vos routes (routes/web.php) importent `App\Http\Controllers\ExpenseOCRController`
 * (namespace racine, sans "Api") — ce fichier est donc placé au bon endroit.
 * Si l'ancien fichier existe encore sous Http/Controllers/Api, supprimez-le
 * pour éviter toute confusion.
 *
 * Le contrat JSON ci-dessous a été ajusté pour correspondre exactement à ce
 * que lit resources/views/expenses/create.blade.php et edit.blade.php :
 *   - result.data.date          (et non result.data.expense_date)
 *   - result.attachment_path    (à la racine, et non result.data.receipt_path)
 *   - result.error              (en plus de result.message, les deux vues n'utilisent pas la même clé)
 *   - result.warnings           (tableau de codes, absent auparavant)
 */
class ExpenseOCRController extends Controller
{
    public function __construct(
        private readonly ExpenseOCRService $expenseOCRService,
    ) {
    }

    /**
     * POST /notes-frais/ocr/scan (route: expenses.ocr.scan)
     */
    public function scan(ScanReceiptRequest $request): JsonResponse
    {
        $file = $request->file('receipt');

        try {
            $result = $this->expenseOCRService->scan($file);
            $receiptPath = $this->expenseOCRService->storeReceipt($file);

            return response()->json([
                'success' => true,
                'data' => $result->toArray(),
                'attachment_path' => $receiptPath,
                'warnings' => $result->warnings(),
            ]);
        } catch (OCRException $e) {
            Log::warning('OCR: échec analyse justificatif', [
                'exception' => $e::class,
                'message' => $e->getMessage(),
                'user_id' => $request->user()?->id,
            ]);

            return response()->json([
                'success' => false,
                'error_code' => class_basename($e),
                'error' => $e->userMessage(),
                'message' => $e->userMessage(),
            ], $e->httpStatusCode());
        } catch (Throwable $e) {
            Log::error('OCR: erreur inattendue', [
                'exception' => $e::class,
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'error_code' => 'UNEXPECTED_ERROR',
                'error' => "Une erreur inattendue est survenue lors de l'analyse du justificatif.",
                'message' => "Une erreur inattendue est survenue lors de l'analyse du justificatif.",
            ], 500);
        }
    }
}
