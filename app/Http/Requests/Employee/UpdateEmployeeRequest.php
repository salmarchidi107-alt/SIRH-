<?php

namespace App\Http\Requests\Employee;

use App\Models\Employee;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;

class UpdateEmployeeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Auth::user() && in_array(Auth::user()->role, ['admin', 'rh']);
    }

    public function rules(): array
    {
        $employeeId = $this->route('employee')->id ?? 0;

        return [
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'email' => ['required', 'email', Rule::unique('employees', 'email')->ignore($employeeId)],
            'phone' => 'nullable|string|max:20',
            'department' => 'required|string|max:100',
            'position' => 'required|string|max:100',

            'diploma_type' => 'nullable|string|max:100',
            'skills' => 'nullable|string',
            'contract_type' => 'required|in:CDI,CDD,Interim,Stage',
            'hire_date' => 'required|date',
            'birth_date' => 'nullable|date',
            'base_salary' => 'nullable|numeric|min:0',
            'status' => 'required|in:active,inactive,leave',
            'cin' => 'nullable|string|max:20',
            'cnss' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'manager_id' => 'nullable|exists:employees,id',
            'children_count' => 'nullable|integer|min:0',
            'payment_method' => 'nullable|in:virement,cash,chèque',
            'bank' => 'nullable|string|max:100',
            'rib' => 'nullable|string|max:30',
            'contractual_benefits' => 'nullable|string',
            'user_id' => ['nullable', 'exists:users,id', Rule::unique('employees', 'user_id')->ignore($employeeId)],
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'department' => 'nullable|string|max:100',
        ];
    }
}

