<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Valide les champs RH une fois relus et éventuellement corrigés par
 * l'utilisateur dans la vue de résultats, avant l'enregistrement définitif.
 *
 * Tous les champs sont "nullable" car l'OCR ne détecte pas systématiquement
 * chaque information selon le type de document (un CV n'a pas de CIN,
 * une CIN n'a pas de "compétences", etc.).
 */
final class ConfirmOcrDataRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Le champ "compétences" arrive du formulaire sous forme de chaîne
     * séparée par des virgules (plus simple côté UI qu'un tableau
     * d'inputs dynamiques) : on la transforme en tableau propre ici,
     * avant que les règles de validation ci-dessous ne s'appliquent.
     */
    protected function prepareForValidation(): void
    {
        if ($this->filled('competences') && is_string($this->input('competences'))) {
            $items = array_map('trim', explode(',', $this->input('competences')));
            $items = array_values(array_filter($items, fn (string $i) => $i !== ''));

            $this->merge(['competences' => $items]);
        }
    }

    public function rules(): array
    {
        return [
            'document_type' => ['required', 'string', 'in:cin,passeport,cv,contrat,diplome,certificat,autre'],
            'nom' => ['nullable', 'string', 'max:100'],
            'prenom' => ['nullable', 'string', 'max:100'],
            'cin' => ['nullable', 'string', 'max:20', 'regex:/^[A-Z]{1,2}\d{1,6}$/i'],
            'date_naissance' => ['nullable', 'date'],
            'sexe' => ['nullable', 'in:M,F'],
            'nationalite' => ['nullable', 'string', 'max:100'],
            'adresse' => ['nullable', 'string', 'max:255'],
            'telephone' => ['nullable', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:255'],
            'numero_document' => ['nullable', 'string', 'max:50'],
            'date_emission' => ['nullable', 'date'],
            'date_expiration' => ['nullable', 'date', 'after_or_equal:date_emission'],
            'diplome' => ['nullable', 'string', 'max:255'],
            'universite' => ['nullable', 'string', 'max:255'],
            'experience_professionnelle' => ['nullable', 'string'],
            'competences' => ['nullable', 'array'],
            'competences.*' => ['string', 'max:60'],
        ];
    }
}
