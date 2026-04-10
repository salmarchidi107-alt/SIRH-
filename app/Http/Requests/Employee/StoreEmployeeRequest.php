<?php

namespace App\Http\Requests\Employee;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;

class StoreEmployeeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Auth::user() && in_array(Auth::user()->role, ['admin', 'rh']);
    }

    public function rules(): array
    {
        return [
            'first_name'           => 'required|string|max:100',
            'last_name'            => 'required|string|max:100',
            'email'                => 'required|email|unique:employees',
            'phone'                => 'nullable|string|max:20',

            // ✅ department est un texte libre (le Creator gère la conversion en department_id)
            'department'           => 'required|string|max:100',
            'department_id'        => 'nullable|exists:departments,id', // optionnel, rempli par le Creator

            'position'             => 'required|string|max:100',
            'diploma_type'         => 'nullable|string|max:100',
            'skills'               => 'nullable|string',
            'contract_type'        => 'required|in:CDI,CDD,Interim,Stage',
            'hire_date'            => 'required|date',
            'birth_date'           => 'nullable|date',
            'base_salary'          => 'nullable|numeric|min:0',
            'status'               => 'required|in:active,inactive,leave',
            'cin'                  => 'nullable|string|max:20',
            'cnss'                 => 'nullable|string|max:20',
            'address'              => 'nullable|string',
            'family_situation'     => 'nullable|string|max:50',
            'manager_id'           => 'nullable|exists:employees,id',
            'children_count'       => 'nullable|integer|min:0',
            'payment_method'       => 'nullable|in:virement,cash,chèque',
            'bank'                 => 'nullable|string|max:100',
            'rib'                  => 'nullable|string|max:30',
            'contractual_benefits' => 'nullable|string',
            'emergency_contact'    => 'nullable|string|max:100',
            'emergency_phone'      => 'nullable|string|max:20',
            'work_hours'           => 'nullable|integer',
            'contract_start_date'  => 'nullable|date',
            'contract_end_date'    => 'nullable|date|after_or_equal:contract_start_date',
            'cp_days'              => 'nullable|integer|min:0',
            'work_hours_counter'   => 'nullable|numeric|min:0',
            'work_days'            => 'nullable|array',
            'work_days.*'          => 'in:lundi,mardi,mercredi,jeudi,vendredi,samedi,dimanche',
            'create_account'       => 'nullable|boolean',
            'user_role'            => ['required_if:create_account,1', Rule::in(['employee', 'rh', 'admin'])],
            'user_password'        => 'required_if:create_account,1|nullable|min:8|confirmed',
            'photo'                => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ];
    }

    public function messages(): array
    {
        return [
            'department.required'    => 'Le département est obligatoire.',
            'first_name.required'    => 'Le prénom est obligatoire.',
            'last_name.required'     => 'Le nom est obligatoire.',
            'email.required'         => 'L\'email est obligatoire.',
            'email.unique'           => 'Cet email est déjà utilisé.',
            'contract_type.required' => 'Le type de contrat est obligatoire.',
            'hire_date.required'     => 'La date d\'embauche est obligatoire.',
            'position.required'      => 'Le poste est obligatoire.',
        ];
    }
}
