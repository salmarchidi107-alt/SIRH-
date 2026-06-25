<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateEmployeeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->user()?->can('manage_employees') ?? false;
    }

    public function rules(): array
    {
        $employee       = $this->route('employee');
        $tenantId       = auth()->user()->tenant_id;
        $changePassword = $this->boolean('change_password');

        return [
            // ── Infos personnelles ──────────────────────────────────────
            'first_name' => 'required|string|max:100',
            'last_name'  => 'required|string|max:100',

            'email' => [
                'required',
                'email',
                Rule::unique('employees', 'email')
                    ->ignore($employee->id)
                    ->where('tenant_id', $tenantId),
            ],

            'phone' => [
                'nullable',
                'string',
                'max:20',
                Rule::unique('employees', 'phone')
                    ->ignore($employee->id)
                    ->where('tenant_id', $tenantId),
            ],

            'cin' => [
                'nullable',
                'string',
                'max:20',
                Rule::unique('employees', 'cin')
                    ->ignore($employee->id)
                    ->where('tenant_id', $tenantId),
            ],

            'birth_date' => [
                'nullable',
                'date',
                'regex:/^\d{4}-\d{2}-\d{2}$/',
                'before:today',
                'after:1900-01-01',
            ],

            // ✅ CORRIGÉ : 'genre' cohérent avec le modèle et la BDD
            'genre'            => 'nullable|in:homme,femme',
            'address'          => 'nullable|string',
            'family_situation' => 'nullable|string|max:50',

            // ── Infos professionnelles ──────────────────────────────────
            'department'    => 'required|string|max:100',
            'position'      => 'required|string|max:100',
            'diploma_type'  => 'nullable|string|max:100',
            'work_site'     => 'nullable|string|max:100',
            'skills'        => 'nullable|string',
            'contract_type' => 'required|string|max:50',

            'hire_date' => [
                'required',
                'date',
                'regex:/^\d{4}-\d{2}-\d{2}$/',
                'after:1900-01-01',
                'before_or_equal:today',
            ],
            'contract_start_date' => [
                'nullable',
                'date',
                'regex:/^\d{4}-\d{2}-\d{2}$/',
                'after:1900-01-01',
            ],
            'contract_end_date' => [
                'nullable',
                'date',
                'regex:/^\d{4}-\d{2}-\d{2}$/',
                'after_or_equal:contract_start_date',
            ],

            'status'     => ['required', Rule::in(['active', 'inactive', 'leave'])],
            'manager_id' => ['nullable', Rule::exists('employees', 'id')->whereNot('id', $employee->id)],

            // ── Liaison compte utilisateur ──────────────────────────────
            'user_id' => ['nullable', Rule::exists('users', 'id')],

            // ── Rémunération & social ───────────────────────────────────
            'base_salary'          => 'nullable|numeric|min:0',
            'cnss'                 => 'nullable|string|max:20',
            'children_count'       => 'nullable|integer|min:0',
            'payment_method'       => ['nullable', Rule::in(['virement', 'cash', 'chèque'])],
            'bank'                 => 'nullable|string|max:100',
            'rib'                  => 'nullable|string|max:30',
            'contractual_benefits' => 'nullable|string',

            // ── Contact d'urgence ───────────────────────────────────────
            'emergency_contact' => 'nullable|string|max:100',
            'emergency_phone'   => 'nullable|string|max:20',

            // ── Contrat de travail ──────────────────────────────────────
            'work_hours'         => 'nullable|numeric|min:0|max:168',
            'work_days'          => 'nullable|array',
            'work_days.*'        => 'string|in:lundi,mardi,mercredi,jeudi,vendredi,samedi,dimanche',
            'cp_days'            => 'nullable|numeric|min:0',
            'conges_anterieurs'  => 'nullable|numeric|min:0|max:999',
            'work_hours_counter' => 'nullable|numeric|min:0',

            // ── Fichiers ────────────────────────────────────────────────
            'photo'        => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'doc_casier'   => 'nullable|file|mimes:pdf|max:2048',
            'doc_rib'      => 'nullable|file|mimes:pdf|max:2048',
            'doc_diplomes' => 'nullable|file|mimes:pdf|max:2048',
            'doc_cin'      => 'nullable|file|mimes:pdf|max:2048',
            'doc_contrat'  => 'nullable|file|mimes:pdf|max:2048',

            // ── Changement de mot de passe (optionnel) ──────────────────
            'change_password' => 'nullable|boolean',
            'new_password'    => [
                $changePassword ? 'required' : 'nullable',
                'string',
                'min:8',
                'confirmed',
            ],

            // ── Permissions ─────────────────────────────────────────────
            'permissions'   => 'nullable|array',
            'permissions.*' => 'nullable|array',

            // ── PIN Badge ───────────────────────────────────────────────
            'pin' => 'nullable|string|size:6|regex:/^[0-9]{4}[A-Z]{2}$/',
        ];
    }

    public function messages(): array
    {
        return [
            'first_name.required'              => 'Le prénom est obligatoire.',
            'last_name.required'               => 'Le nom est obligatoire.',
            'email.required'                   => "L'adresse email est obligatoire.",
            'email.unique'                     => 'Cette adresse email est déjà utilisée par un autre employé.',
            'phone.unique'                     => 'Ce numéro de téléphone est déjà utilisé par un autre employé.',
            'cin.unique'                       => 'Ce numéro CIN est déjà associé à un autre employé.',
            'hire_date.required'               => "La date d'embauche est obligatoire.",
            'hire_date.regex'                  => "L'année de la date d'embauche doit contenir exactement 4 chiffres.",
            'hire_date.before_or_equal'        => "La date d'embauche ne peut pas être dans le futur.",
            'birth_date.regex'                 => "L'année de la date de naissance doit contenir exactement 4 chiffres.",
            'birth_date.before'                => 'La date de naissance doit être dans le passé.',
            'contract_start_date.regex'        => "L'année du début de contrat doit contenir exactement 4 chiffres.",
            'contract_end_date.regex'          => "L'année de fin de contrat doit contenir exactement 4 chiffres.",
            'contract_end_date.after_or_equal' => 'La date de fin doit être postérieure à la date de début.',
            'department.required'              => 'Le département est obligatoire.',
            'position.required'                => 'Le poste est obligatoire.',
            'contract_type.required'           => 'Le type de contrat est obligatoire.',
            'status.required'                  => 'Le statut est obligatoire.',
            'status.in'                        => 'Statut invalide.',
            'new_password.required'            => 'Le nouveau mot de passe est obligatoire.',
            'new_password.min'                 => 'Le mot de passe doit contenir au moins 8 caractères.',
            'new_password.confirmed'           => 'Les mots de passe ne correspondent pas.',
            'pin.regex'                        => 'Le PIN doit contenir 4 chiffres suivis de 2 lettres majuscules (ex : 1234AB).',
        ];
    }
}
