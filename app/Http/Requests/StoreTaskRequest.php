<?php

namespace App\Http\Requests;

use App\Models\Task;
use App\Support\Duration;
use Illuminate\Foundation\Http\FormRequest;

class StoreTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // tout utilisateur connecté peut créer sa propre tâche
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string', 'max:2000'],
            'priority' => ['required', 'in:' . implode(',', Task::PRIORITIES)],
            'due_date' => ['nullable', 'date'],
            'estimated_duration' => ['nullable', 'string', 'max:20'],
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => "Le titre de la tâche est obligatoire.",
            'priority.in' => "Priorité invalide.",
        ];
    }

    /** Retourne les données prêtes pour Task::create(), estimation convertie en minutes. */
    public function validatedForCreate(int $projectId, int $userId): array
    {
        $data = $this->validated();

        return [
            'project_id' => $projectId,
            'user_id' => $userId,
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'priority' => $data['priority'],
            'due_date' => $data['due_date'] ?? null,
            'estimated_minutes' => Duration::toMinutes($data['estimated_duration'] ?? null),
            'status' => 'a_faire',
        ];
    }
}
