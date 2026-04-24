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
        return [
            // ── Identité ──────────────────────────────────────────────────
            'first_name'      => 'required|string|max:100',
            'last_name'       => 'required|string|max:100',
            'email'           => 'required|email|unique:employees',
            'phone'           => 'nullable|string|max:20',
            'birth_date'      => 'nullable|date',
            'cin'             => 'nullable|string|max:20',
            'family_situation'=> 'nullable|string|max:50',
            'address'         => 'nullable|string',
            'photo'           => 'nullable|image|max:2048',

            // ── Professionnel ─────────────────────────────────────────────
            'department'      => 'required|string|max:100',
            'position'        => 'required|string|max:100',
            'diploma_type'    => 'nullable|string|max:100',
            'skills'          => 'nullable|string',
            'contract_type'   => ['required', Rule::in(['CDI','CDD','Interim','Stage','Freelance'])],
            'hire_date'       => 'required|date',
            'status'          => ['required', Rule::in(['active','inactive','leave'])],
            'manager_id'      => 'nullable|exists:employees,id',

            // ── Contrat ───────────────────────────────────────────────────
            'work_hours'          => 'nullable|numeric|min:0',
            'contract_start_date' => 'nullable|date',
            'contract_end_date'   => 'nullable|date|after_or_equal:contract_start_date',
            'work_days'           => 'nullable|array',
            'work_days.*'         => 'string',
            'cp_days'             => 'nullable|integer|min:0',
            'work_hours_counter'  => 'nullable|numeric|min:0',

            // ── Rémunération & Social ─────────────────────────────────────
            'base_salary'          => 'nullable|numeric|min:0',
            'cnss'                 => 'nullable|string|max:20',
            'children_count'       => 'nullable|integer|min:0',
            'payment_method'       => ['nullable', Rule::in(['virement','cash','chèque'])],
            'bank'                 => 'nullable|string|max:100',
            'rib'                  => 'nullable|string|max:30',
            'contractual_benefits' => 'nullable|string',

            // ── Urgence ───────────────────────────────────────────────────
            'emergency_contact' => 'nullable|string|max:100',
            'emergency_phone'   => 'nullable|string|max:20',

            // ── Compte utilisateur ────────────────────────────────────────
            'create_account' => 'nullable|boolean',
            'user_role'      => [
                Rule::requiredIf(fn () => $this->boolean('create_account')),
                Rule::in(array_keys(config('roles.roles', []))),
            ],
            'user_password' => ['required_if:create_account,true', 'string', 'min:8', 'confirmed'],
            'user_id'       => 'nullable|exists:users,id',

            // ── Badge ─────────────────────────────────────────────────────
            'pin' => 'nullable|string|size:6|regex:/^[0-9]{4}[A-Z]{2}$/',
        ];
    }
}
