<?php
// ── UpdatePlanningRequest.php ─────────────────────────────────────────────────
// Chemin : app/Http/Requests/UpdatePlanningRequest.php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\Planning;

class UpdatePlanningRequest extends FormRequest
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
        return [
            'shift_start' => 'required|string',
            'shift_end'   => 'required|string',
            'shift_type'  => ['required', Rule::in(array_keys(Planning::SHIFT_TYPES))],
            'notes'       => 'nullable|string',
            'room'        => 'nullable|string|max:255',
        ];
    }
}