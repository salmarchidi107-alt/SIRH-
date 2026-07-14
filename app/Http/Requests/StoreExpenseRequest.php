<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreExpenseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'category' => ['nullable', 'string', 'max:100'],
            'expense_date' => ['required', 'date', 'before_or_equal:today'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'currency' => ['required', 'string', 'size:3'],
            'description' => ['nullable', 'string', 'max:2000'],
            // Chemin déjà stocké via /api/ocr/scan, pas un nouvel upload direct sur ce endpoint.
            'receipt_path' => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'Le titre est obligatoire.',
            'expense_date.required' => 'La date de la dépense est obligatoire.',
            'expense_date.before_or_equal' => 'La date ne peut pas être dans le futur.',
            'amount.required' => 'Le montant est obligatoire.',
            'amount.min' => 'Le montant doit être supérieur à 0.',
            'currency.size' => 'La devise doit être un code ISO à 3 lettres (EUR, USD...).',
        ];
    }
}
