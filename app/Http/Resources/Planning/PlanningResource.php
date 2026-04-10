<?php

namespace App\Http\Resources\Planning;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PlanningResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'employee_id' => $this->employee_id,
            'date' => $this->date->format('Y-m-d'),
            'shift_start' => $this->shift_start,
            'shift_end' => $this->shift_end,
            'shift_type' => $this->shift_type,
            'notes' => $this->notes,
            'formatted_type' => self::SHIFT_TYPES[$this->shift_type] ?? $this->shift_type,
            'color' => $this->resolveShiftColor($this->shift_type),
            'employee' => [
                'id' => $this->whenLoaded('employee')->employee?->id,
                'full_name' => $this->whenLoaded('employee')->employee?->full_name,
                'department' => $this->whenLoaded('employee')->employee?->department,
            ],
        ];
    }

    private function resolveShiftColor(string $type): string
    {
        return match($type) {
            'matin', 'journee' => '#0ea5e9',
            'apres_midi' => '#f59e0b',
            'nuit' => '#6366f1',
            'garde' => '#ef4444',
            default => '#10b981',
        };
    }

    public const SHIFT_TYPES = [
        'matin' => 'Matin',
        'apres_midi' => 'Après-midi',
        'nuit' => 'Nuit',
        'journee' => 'Journée',
        'garde' => 'Garde',
    ];
}

