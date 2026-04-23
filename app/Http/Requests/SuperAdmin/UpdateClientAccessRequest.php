<?php

namespace App\Http\Requests\SuperAdmin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateClientAccessRequest extends FormRequest
{

    public function authorize(): bool
    {
        return true;


    public function rules(): array
    {
        $userId = $this->route('user')?->id;

        return [
            'email'    => [
                'nullable',
                'string',
                'email',
                'max:255',
                "unique:users,email,{$userId}",
            ],
            'password' => [
                'nullable',
                'string',
                'min:8',
                'confirmed',
            ],
        ];
    }


    public function messages(): array
    {
        return [
            'email.email'    => "L'adresse email n'est pas valide.",
            'email.unique'   => "Cette adresse email est déjà utilisée.",
            'password.min'   => "Le mot de passe doit contenir au moins 8 caractères.",
            'password.confirmed' => "Les mots de passe ne correspondent pas.",
        ];
    }


    public function attributes(): array
    {
        return [
            'email'    => 'adresse email',
            'password' => 'mot de passe',
        ];
    }
}
