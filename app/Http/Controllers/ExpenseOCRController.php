<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Exceptions\OCR\OcrException;
use App\Http\Requests\ScanReceiptRequest;
use App\Services\ExpenseOCRService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Throwable;

class ExpenseOCRController extends Controller
{
    public function __construct(
        private readonly ExpenseOCRService $expenseOCRService,
    ) {
    }


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
