<?php

namespace App\Http\Requests;

use App\Models\Task;
use App\Support\Duration;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreAdminTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    public function rules(): array
    {
        return [
            'project_id' => ['required', 'integer', 'exists:projects,id'],
            'assigned_to' => ['required', 'integer', 'exists:users,id'],
            'title' => ['required', 'string', 'max:200'],
            'description' => ['nullable', 'string', 'max:5000'],
            'priority' => ['required', 'in:' . implode(',', Task::PRIORITIES)],
            'status' => ['nullable', 'in:' . implode(',', Task::STATUSES)],
            'start_date' => ['nullable', 'date'],
            'due_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'estimated_duration' => ['nullable', 'string', 'max:20'],
        ];
    }

    public function messages(): array
    {
        return [
            'project_id.required' => "Le projet est obligatoire.",
            'assigned_to.required' => "L'employé assigné est obligatoire.",
            'title.required' => "Le nom de la tâche est obligatoire.",
            'due_date.after_or_equal' => "L'échéance doit être postérieure ou égale à la date de début.",
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            if ($this->filled('estimated_duration') && Duration::toMinutes($this->input('estimated_duration')) === null) {
                $validator->errors()->add('estimated_duration', "Format invalide. Exemples valides : 4h, 1h30, 45m.");
            }
        });
    }

    public function validatedForCreate(int $creatorUserId): array
    {
        $data = $this->validated();

        return [
            'project_id' => $data['project_id'],
            'user_id' => $creatorUserId,
            'assigned_to' => $data['assigned_to'],
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'priority' => $data['priority'],
            'status' => $data['status'] ?? 'a_faire',
            'start_date' => $data['start_date'] ?? null,
            'due_date' => $data['due_date'] ?? null,
            'estimated_minutes' => Duration::toMinutes($data['estimated_duration'] ?? null),
        ];
    }

    public function validatedForUpdate(): array
    {
        $data = $this->validated();

        return [
            'project_id' => $data['project_id'],
            'assigned_to' => $data['assigned_to'],
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'priority' => $data['priority'],
            'status' => $data['status'] ?? 'a_faire',
            'start_date' => $data['start_date'] ?? null,
            'due_date' => $data['due_date'] ?? null,
            'estimated_minutes' => Duration::toMinutes($data['estimated_duration'] ?? null),
        ];
    }
}
