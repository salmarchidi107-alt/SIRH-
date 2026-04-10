<?php

namespace App\Http\Requests\SuperAdmin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateClientAccessRequest extends FormRequest
{
    /**
     * Seuls les Super Admins peuvent effectuer cette action.
     * Le middleware SuperAdmin protège déjà la route,
     * mais on double la vérification ici par sécurité.
     */
    public function authorize(): bool
    {
        $user = auth()->user();

        return $user && ($user->role === 'superadmin' || is_null($user->tenant_id));
    }

    /**
     * Règles de validation.
     * Au moins email OU password doit être fourni.
     */
    public function rules(): array
    {
        return [
            'email'    => ['nullable', 'email', 'max:255', 'required_without:password'],
            'password' => ['nullable', 'string', 'min:8', 'confirmed', 'required_without:email'],
        ];
    }

    public function messages(): array
    {
        return [
            'email.email'               => "L'adresse email n'est pas valide.",
            'email.required_without'    => 'Renseignez un email ou un mot de passe.',
            'password.min'              => 'Le mot de passe doit contenir au moins 8 caractères.',
            'password.confirmed'        => 'Les mots de passe ne correspondent pas.',
            'password.required_without' => 'Renseignez un email ou un mot de passe.',
        ];
    }

    /**
     * Prépare les données : checkbox → boolean, trim email
     */
    protected function prepareForValidation(): void
    {
        if ($this->has('email')) {
            $this->merge(['email' => trim($this->email)]);
        }
    }
}
