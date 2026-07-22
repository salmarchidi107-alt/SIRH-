<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProjectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // tout utilisateur connecté peut créer son propre projet
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string', 'max:2000'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => "Le nom du projet est obligatoire.",
        ];
    }

    public function validatedForCreate(int $userId): array
    {
        $data = $this->validated();

        return [
            'user_id' => $userId,
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'status' => 'actif',
        ];
    }
}
