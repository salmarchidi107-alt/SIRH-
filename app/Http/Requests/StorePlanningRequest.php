<?php
// ── StorePlanningRequest.php ──────────────────────────────────────────────────
// Chemin : app/Http/Requests/StorePlanningRequest.php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\Planning;

class StorePlanningRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = auth()->user();
        if (!$user) return false;

        // Admin et RH ont toujours accès
        if (in_array($user->role, ['admin', 'rh'])) return true;

        // Autres rôles : vérifier la permission granulaire
        return $user->can('manage_plannings');
    }

    public function rules(): array
    {
        $rules = [
            'employee_id' => 'required|exists:employees,id',
            'date'        => 'required|date',
            'shift_start' => 'required|string',
            'shift_end'   => 'required|string',
            'shift_type'  => ['required', Rule::in(array_keys(Planning::SHIFT_TYPES))],
            'notes'       => 'nullable|string',
            'room'        => 'nullable|string|max:255',
        ];

        // Bloquer la création si l'employé a une absence approuvée ce jour-là
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
