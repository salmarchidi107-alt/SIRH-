<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreEmployeeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->user()?->can('manage_employees') ?? false;
    }

    public function rules(): array
    {
        $createAccount = $this->boolean('create_account');
        $tenantId      = auth()->user()->tenant_id;

        return [
            // ── Infos personnelles ──────────────────────────────────────
            'first_name'       => 'required|string|max:100',
            'last_name'        => 'required|string|max:100',

            'email' => [
                'required',
                'email',
                Rule::unique('employees', 'email')
                    ->where('tenant_id', $tenantId),
            ],

            'phone' => [
    'nullable',
    'string',
    'max:20',
    Rule::unique('employees', 'phone')
        ->where('tenant_id', $tenantId),
],

            'birth_date'       => ['nullable', 'date', 'before:today', 'after:1900-01-01'],
            'genre'            => 'nullable|in:homme,femme',

            'cin' => [
    'nullable',
    'string',
    'max:20',
    Rule::unique('employees', 'cin')
        ->where('tenant_id', $tenantId),
],

            'address'          => 'nullable|string',
            'family_situation' => 'nullable|string|max:50',

            // ── Infos professionnelles ──────────────────────────────────
            'department'   => 'required|string|max:100',
            'position'     => 'required|string|max:100',
            'diploma_type' => 'nullable|string|max:100',
            'work_site'    => 'nullable|string|max:100',
            'skills'       => 'nullable|string',

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
            'birth_date' => [
                'nullable',
                'date',
                'regex:/^\d{4}-\d{2}-\d{2}$/',
                'before:today',
                'after:1900-01-01',
            ],

            'status'     => ['required', Rule::in(['active', 'inactive', 'leave'])],
            'manager_id' => 'nullable|exists:employees,id',

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

            // ── Compte utilisateur ──────────────────────────────────────
            'create_account' => 'nullable|boolean',
            'user_role' => [
                $createAccount ? 'required' : 'nullable',
                'string',
            ],
            'user_password' => [
                $createAccount ? 'required' : 'nullable',
                'string',
                'min:8',
                'confirmed',
            ],
            'user_id' => 'nullable|exists:users,id',

            // ── PIN Badge ───────────────────────────────────────────────
            'pin' => 'nullable|string|size:6|regex:/^[0-9]{4}[A-Z]{2}$/',
        ];
    }

    public function messages(): array
    {
        return [
            // Champs obligatoires
            'first_name.required'     => 'Le prénom est obligatoire.',
            'last_name.required'      => 'Le nom est obligatoire.',
            'email.required'          => 'L\'adresse email est obligatoire.',
            'department.required'     => 'Le département est obligatoire.',
            'position.required'       => 'Le poste est obligatoire.',
            'contract_type.required'  => 'Le type de contrat est obligatoire.',
            'hire_date.required'      => 'La date d\'embauche est obligatoire.',
            'status.required'         => 'Le statut est obligatoire.',

            // Unicité
            'email.unique'  => 'Cette adresse email est déjà utilisée par un autre employé.',
            'phone.unique'  => 'Ce numéro de téléphone est déjà utilisé par un autre employé.',
            'cin.unique'    => 'Ce numéro CIN est déjà associé à un autre employé.',

            // Dates
            'hire_date.regex'             => 'L\'année de la date d\'embauche doit contenir exactement 4 chiffres.',
            'hire_date.before_or_equal'   => 'La date d\'embauche ne peut pas être dans le futur.',
            'birth_date.regex'            => 'L\'année de la date de naissance doit contenir exactement 4 chiffres.',
            'birth_date.before'           => 'La date de naissance doit être dans le passé.',
            'birth_date.after'            => 'La date de naissance semble invalide.',
            'contract_start_date.regex'   => 'L\'année du début de contrat doit contenir exactement 4 chiffres.',
            'contract_end_date.regex'     => 'L\'année de fin de contrat doit contenir exactement 4 chiffres.',
            'contract_end_date.after_or_equal' => 'La date de fin de contrat doit être postérieure à la date de début.',

            // Compte utilisateur
            'user_role.required'      => 'Le rôle est obligatoire pour créer un compte.',
            'user_password.required'  => 'Le mot de passe est obligatoire pour créer un compte.',
            'user_password.min'       => 'Le mot de passe doit contenir au moins 8 caractères.',
            'user_password.confirmed' => 'Les mots de passe ne correspondent pas.',

            // Fichiers
            'photo.image' => 'Le fichier doit être une image (jpg, jpeg, png, webp).',
            'photo.max'   => 'La photo ne doit pas dépasser 2 Mo.',
            'pin.regex'   => 'Le PIN doit contenir 4 chiffres suivis de 2 lettres majuscules (ex : 1234AB).',

            'status.in' => 'Statut invalide.',
        ];
    }

    public function attributes(): array
    {
        return [
            'first_name'          => 'prénom',
            'last_name'           => 'nom',
            'email'               => 'email',
            'phone'               => 'téléphone',
            'birth_date'          => 'date de naissance',
            'cin'                 => 'CIN',
            'address'             => 'adresse',
            'family_situation'    => 'situation familiale',
            'department'          => 'département',
            'position'            => 'poste',
            'diploma_type'        => 'type de diplôme',
            'work_site'           => 'site de travail',
            'skills'              => 'compétences',
            'contract_type'       => 'type de contrat',
            'hire_date'           => 'date d\'embauche',
            'status'              => 'statut',
            'manager_id'          => 'responsable',
            'base_salary'         => 'salaire de base',
            'cnss'                => 'numéro CNSS',
            'children_count'      => 'nombre d\'enfants',
            'payment_method'      => 'mode de paiement',
            'bank'                => 'banque',
            'rib'                 => 'RIB',
            'contractual_benefits'=> 'avantages contractuels',
            'emergency_contact'   => 'contact d\'urgence',
            'emergency_phone'     => 'téléphone d\'urgence',
            'work_hours'          => 'heures de travail',
            'contract_start_date' => 'début du contrat',
            'contract_end_date'   => 'fin du contrat',
            'work_days'           => 'jours de travail',
            'cp_days'             => 'congés payés',
            'work_hours_counter'  => 'compteur d\'heures',
            'photo'               => 'photo',
            'user_role'           => 'rôle utilisateur',
            'user_password'       => 'mot de passe',
            'pin'                 => 'code PIN',
        ];
    }
}
