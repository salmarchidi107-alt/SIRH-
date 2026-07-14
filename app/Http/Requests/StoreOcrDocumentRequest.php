<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Valide le document RH envoyé pour analyse OCR.
 *
 * - Formats acceptés : PDF, JPG, JPEG, PNG (conformément au cahier des charges)
 * - Taille max : 10 Mo (raisonnable pour un scan/photo de document RH ;
 *   configurable ci-dessous si besoin)
 */
final class StoreOcrDocumentRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Adapter si une policy/permission Spatie doit restreindre l'accès,
        // ex: return $this->user()?->can('ocr.upload');
        return true;
    }

    public function rules(): array
    {
        return [
            'document' => [
                'required',
                'file',
                'mimes:pdf,jpg,jpeg,png',
                'max:10240', // 10 Mo, en Ko
            ],
            'document_type' => [
                'nullable',
                'string',
                'in:cin,passeport,cv,contrat,diplome,certificat,autre',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'document.required' => 'Veuillez sélectionner un document à analyser.',
            'document.file' => 'Le fichier envoyé est invalide.',
            'document.mimes' => 'Seuls les formats PDF, JPG et PNG sont acceptés.',
            'document.max' => 'Le fichier ne doit pas dépasser 10 Mo.',
            'document_type.in' => 'Type de document non reconnu.',
        ];
    }
}
