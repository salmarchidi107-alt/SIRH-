<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAdminProjectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string', 'max:2000'],
            'status' => ['nullable', 'in:actif,archive'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => "Le nom du projet est obligatoire.",
        ];
    }

    public function validatedForCreate(int $adminUserId): array
    {
        $data = $this->validated();

        return [
            'user_id' => $adminUserId,
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'status' => $data['status'] ?? 'actif',
        ];
    }

    public function validatedForUpdate(): array
    {
        $data = $this->validated();

        return [
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'status' => $data['status'] ?? 'actif',
        ];
    }
}
