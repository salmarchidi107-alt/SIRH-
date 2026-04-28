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
        $employee = $this->route('employee');

        return [
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'email' => [
                'required',
                'email',
                Rule::unique('employees', 'email')->ignore($employee->id),
            ],
            'phone' => 'nullable|string|max:20',
            'department' => 'required|string|max:100',
            'position' => 'required|string|max:100',
            'diploma_type' => 'nullable|string|max:100',
            'skills' => 'nullable|string',
            'contract_type' => ['required', Rule::in(['CDI','CDD','Interim','Stage'])],
            'hire_date' => 'required|date',
            'birth_date' => 'nullable|date',
            'base_salary' => 'nullable|numeric|min:0',
            'status' => ['required', Rule::in(['active','inactive','leave'])],
            'cin' => 'nullable|string|max:20',
            'cnss' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'manager_id' => 'nullable|exists:employees,id',
            'children_count' => 'nullable|integer|min:0',
            'family_situation' => 'nullable|string|max:50',
            'diploma_type' => 'nullable|string|max:100',
            'work_hours' => 'nullable|numeric|min:0',
            'work_days' => 'nullable|array',
            'contract_start_date' => 'nullable|date',
            'contract_end_date' => 'nullable|date|after_or_equal:contract_start_date',
            'payment_method' => ['nullable', Rule::in(['virement','cash','chèque'])],
            'bank' => 'nullable|string|max:100',
            'rib' => 'nullable|string|max:30',
            'contractual_benefits' => 'nullable|string',
            'photo' => 'nullable|image|max:2048',
            'user_id' => [
                'nullable',
                Rule::unique('employees', 'user_id')->ignore($employee->id),
            ],
            'pin' => 'nullable|string|size:6|regex:/^[0-9]{4}[A-Z]{2}$/',
            'signature' => 'nullable|string',
        ];
    }
}

