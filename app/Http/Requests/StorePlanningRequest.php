<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\Planning;

class StorePlanningRequest extends FormRequest
{
    public function authorize(): bool
    {
       return true; // return auth()->user()?->can('manage_plannings') ?? true;
    }

    public function rules(): array
    {
        $rules = [
            'employee_id' => 'required|exists:employees,id',
            'date' => 'required|date',
            'shift_start' => 'required|string',
            'shift_end' => 'required|string',
            'shift_type' => ['required', Rule::in(array_keys(Planning::SHIFT_TYPES))],
            'notes' => 'nullable|string',
            'room' => 'nullable|string|max:255',
        ];

        // Prevent planning on approved absence days
        if ($this->date && $this->employee_id) {
            $employee = \App\Models\Employee::find($this->employee_id);
            if ($employee && $employee->hasApprovedAbsenceOn($this->date)) {
                $rules['date'] = 'required|date|bail|prohibited';
                $this->merge(['absence_conflict' => true]);
            }
        }

        return $rules;
    }
}
