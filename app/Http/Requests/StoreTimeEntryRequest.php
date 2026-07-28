<?php

namespace App\Http\Requests;

use App\Models\Activity;
use App\Models\Task;
use App\Support\Duration;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreTimeEntryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // vérifié dans le contrôleur (tâche bien assignée à l'employé connecté)
    }

    public function rules(): array
    {
        return [
            'project_id' => ['required', 'integer', 'exists:projects,id'],
            'task_id' => ['required', 'integer', 'exists:tasks,id'],
            'activity_date' => ['required', 'date'],
            'start_time' => ['nullable', 'date_format:H:i'],
            'end_time' => ['nullable', 'date_format:H:i', 'after:start_time'],
            'duration' => ['nullable', 'string', 'max:20'],
            'comment' => ['required', 'string', 'max:2000'],
        ];
    }

    public function messages(): array
    {
        return [
            'task_id.required' => "La tâche est obligatoire.",
            'comment.required' => "Merci de décrire le travail réalisé.",
            'end_time.after' => "L'heure de fin doit être postérieure à l'heure de début.",
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $hasRange = $this->filled('start_time') && $this->filled('end_time');
            $hasDuration = $this->filled('duration');

            if (! $hasRange && ! $hasDuration) {
                $validator->errors()->add('duration', "Renseigne soit une heure de début/fin, soit une durée.");
                return;
            }

            if ($hasDuration && Duration::toMinutes($this->input('duration')) === null) {
                $validator->errors()->add('duration', "Format de durée invalide. Exemples valides : 1h30, 45m, 2h.");
            }
        });
    }

    private function computeMinutes(): int
    {
        if ($this->filled('start_time') && $this->filled('end_time')) {
            [$sh, $sm] = explode(':', $this->input('start_time'));
            [$eh, $em] = explode(':', $this->input('end_time'));

            return (((int) $eh * 60) + (int) $em) - (((int) $sh * 60) + (int) $sm);
        }

        return Duration::toMinutes($this->input('duration')) ?? 0;
    }

    public function validatedForCreate(Task $task, int $userId): array
    {
        $data = $this->validated();

        return [
            'tenant_id' => $task->tenant_id,
            'task_id' => $task->id,
            'user_id' => $userId,
            'type' => 'manuelle',
            'activity_date' => $data['activity_date'],
            'start_time' => $data['start_time'] ?? null,
            'end_time' => $data['end_time'] ?? null,
            'duration_minutes' => max(1, $this->computeMinutes()),
            'comment' => $data['comment'],
            'status' => 'soumise',
        ];
    }
}
