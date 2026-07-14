<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Valide le fichier envoyé pour analyse OCR avant qu'il n'atteigne le contrôleur.
 * Toute règle de validation (format, taille) est centralisée ici, jamais dans le contrôleur.
 */
class ScanReceiptRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        $maxSizeKb = (int) config('services.ocr.max_upload_size_kb', 10240); // 10 Mo par défaut

        return [
            'receipt' => [
                'required',
                'file',
                'mimes:jpg,jpeg,png,pdf',
                'max:'.$maxSizeKb,
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'receipt.required' => 'Veuillez sélectionner un justificatif.',
            'receipt.file' => 'Le fichier envoyé est invalide.',
            'receipt.mimes' => 'Format non supporté. Formats acceptés : JPG, PNG, PDF.',
            'receipt.max' => 'Le fichier dépasse la taille maximale autorisée.',
        ];
    }
}
