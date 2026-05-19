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

        return [
            // ── Infos personnelles ─────────────────────────────────────────
            'first_name'       => 'required|string|max:100',
            'last_name'        => 'required|string|max:100',
            'email'            => 'required|email|unique:employees,email',
            'phone'            => 'nullable|string|max:20',
            'birth_date'       => 'nullable|date',
            'cin'              => 'nullable|string|max:20',
            'address'          => 'nullable|string',
            'family_situation' => 'nullable|string|max:50',

            // ── Infos professionnelles ────────────────────────────────────
            'department'   => 'required|string|max:100',
            'position'     => 'required|string|max:100',
            'diploma_type' => 'nullable|string|max:100',
            'work_site'    => 'nullable|string|max:100',
            'skills'       => 'nullable|string',

            'contract_type' => ['required', Rule::in(['CDI', 'CDD', 'Interim', 'Stage'])],
            'hire_date'     => 'required|date',
            'status'        => ['required', Rule::in(['active', 'inactive', 'leave'])],
            'manager_id'    => 'nullable|exists:employees,id',

            // ── Rémunération & social ─────────────────────────────────────
            'base_salary'          => 'nullable|numeric|min:0',
            'cnss'                 => 'nullable|string|max:20',
            'children_count'       => 'nullable|integer|min:0',
            'payment_method'       => ['nullable', Rule::in(['virement', 'cash', 'chèque'])],
            'bank'                 => 'nullable|string|max:100',
            'rib'                  => 'nullable|string|max:30',
            'contractual_benefits' => 'nullable|string',

            // ── Contact d'urgence ─────────────────────────────────────────
            'emergency_contact' => 'nullable|string|max:100',
            'emergency_phone'   => 'nullable|string|max:20',

            // ── Contrat de travail ────────────────────────────────────────
            'work_hours'          => 'nullable|numeric|min:0',
            'contract_start_date' => 'nullable|date',
            'contract_end_date'   => 'nullable|date|after_or_equal:contract_start_date',
            'work_days'           => 'nullable|array',
            'work_days.*'         => 'string|in:lundi,mardi,mercredi,jeudi,vendredi,samedi,dimanche',
            'cp_days'             => 'nullable|numeric|min:0',
            'work_hours_counter'  => 'nullable|numeric|min:0',

            // ── Fichiers ──────────────────────────────────────────────────
            'photo' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',

            // ── Compte utilisateur (optionnel) ────────────────────────────
            'create_account' => 'nullable|boolean',
            'user_role'      => [
                $createAccount ? 'required' : 'nullable',
                Rule::when(
                    $createAccount,
                    [Rule::in(array_keys(config('roles.roles', ['employee' => 'Employé', 'rh' => 'RH', 'admin' => 'Admin'])))]
                ),
            ],
            'user_password' => [
                $createAccount ? 'required' : 'nullable',
                'string',
                'min:8',
                'confirmed',
            ],
            'user_id' => 'nullable|exists:users,id',

            // ── PIN Badge ─────────────────────────────────────────────────
            'pin' => 'nullable|string|size:6|regex:/^[0-9]{4}[A-Z]{2}$/',
        ];
    }

    public function messages(): array
    {
        return [
            'first_name.required'    => 'Le prénom est obligatoire.',
            'last_name.required'     => 'Le nom est obligatoire.',
            'email.required'         => 'L\'email est obligatoire.',
            'email.unique'           => 'Cet email est déjà utilisé.',
            'department.required'    => 'Le département est obligatoire.',
            'position.required'      => 'Le poste est obligatoire.',
            'contract_type.required' => 'Le type de contrat est obligatoire.',
            'contract_type.in'       => 'Type de contrat invalide (CDI, CDD, Interim, Stage).',
            'hire_date.required'     => 'La date d\'embauche est obligatoire.',
            'status.required'        => 'Le statut est obligatoire.',
            'status.in'              => 'Statut invalide.',
            'user_role.required'     => 'Le rôle utilisateur est obligatoire si vous créez un compte.',
            'user_password.required' => 'Le mot de passe est obligatoire si vous créez un compte.',
            'user_password.min'      => 'Le mot de passe doit contenir au moins 8 caractères.',
            'user_password.confirmed'=> 'Les mots de passe ne correspondent pas.',
            'photo.image'            => 'Le fichier doit être une image.',
            'photo.max'              => 'La photo ne doit pas dépasser 2 Mo.',
            'pin.regex'              => 'Le PIN doit contenir 4 chiffres suivis de 2 lettres majuscules (ex: 1234AB).',
            'contract_end_date.after_or_equal' => 'La date de fin doit être après la date de début.',
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
