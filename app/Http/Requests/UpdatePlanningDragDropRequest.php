<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePlanningDragDropRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Auth middleware + role protects
    }


    public function rules(): array
    {
        return [
            'planning_id' => 'required|exists:plannings,id',
            'new_date' => 'required|date',
            'new_employee_id' => 'nullable|exists:employees,id',
            'duplicate' => 'boolean',
        ];
    }
}
