<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\Planning;

class UpdatePlanningRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->user()?->can('manage_plannings') ?? true;
    }

    public function rules(): array
    {
        return [
            'shift_start' => 'required|string',
            'shift_end' => 'required|string',
            'shift_type' => ['required', Rule::in(array_keys(Planning::SHIFT_TYPES))],
            'notes' => 'nullable|string',
        ];
    }
}
