<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Absence;

class StoreAbsenceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'employee_id' => 'required|exists:employees,id',
            'type' => 'required|in:' . implode(',', array_keys(Absence::TYPES)),
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'reason' => 'nullable|string',
            'replacement_id' => 'nullable|exists:employees,id',
            'notes' => 'nullable|string',
        ];
    }
}
