<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Formation;

class FormationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'employee_id'  => ['required', 'exists:employees,id'],
            'titre'        => ['required', 'string', 'max:255'],
            'formateur'    => ['required', 'string', 'max:255'],
            'organisme'    => ['required', 'string', 'max:255'],
            'date'         => ['required', 'date'],
            'heure_debut'  => ['required', 'date_format:H:i'],
            'heure_fin'    => ['required', 'date_format:H:i', 'after:heure_debut'],
            'statut'       => ['required', 'in:' . implode(',', Formation::STATUTS)],
            'description'  => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'employee_id.required'  => 'Veuillez sélectionner un employé.',
            'employee_id.exists'    => 'L\'employé sélectionné est introuvable.',
            'titre.required'        => 'Le titre de la formation est obligatoire.',
            'formateur.required'    => 'Le formateur est obligatoire.',
            'organisme.required'    => 'L\'organisme est obligatoire.',
            'date.required'         => 'La date est obligatoire.',
            'date.date'             => 'La date n\'est pas valide.',
            'heure_debut.required'  => 'L\'heure de début est obligatoire.',
            'heure_fin.required'    => 'L\'heure de fin est obligatoire.',
            'heure_fin.after'       => 'L\'heure de fin doit être après l\'heure de début.',
            'statut.required'       => 'Le statut est obligatoire.',
            'statut.in'             => 'Le statut sélectionné n\'est pas valide.',
        ];
    }
}
