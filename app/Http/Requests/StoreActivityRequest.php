<?php

namespace App\Http\Requests;

use App\Support\Duration;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreActivityRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // vérifié en amont par la policy dans le contrôleur (tâche du user courant)
    }

    public function rules(): array
    {
        return [
            'activity_date' => ['required', 'date'],
            'duration' => ['required', 'string', 'max:20'],
            'comment' => ['nullable', 'string', 'max:2000'],
            'attachment' => ['nullable', 'file', 'max:10240'], // 10 Mo
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            if ($this->filled('duration') && Duration::toMinutes($this->input('duration')) === null) {
                $validator->errors()->add('duration', "Format de durée invalide. Exemples valides : 1h30, 45m, 2h.");
            }
        });
    }

    public function durationInMinutes(): int
    {
        return Duration::toMinutes($this->validated()['duration']);
    }
}
