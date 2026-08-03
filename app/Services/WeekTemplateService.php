<?php

namespace App\Services;

use App\Models\Absence;
use App\Models\Employee;
use App\Models\Room;
use App\Models\WeekTemplate;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class WeekTemplateService
{

    public function getIndexData(): array
    {
        return ['templates' => WeekTemplate::all()];
    }

    public function getCreateData(): array
    {
        return ['rooms' => Room::all()];
    }


    public function resolveRoomNames(array $validated): array
    {
        $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];

        foreach ($days as $day) {
            $roomId = $validated[$day . '_room'];
            if ($roomId) {
                $room = Room::find($roomId);
                $validated[$day . '_room'] = $room ? $room->name : null;
            } else {
                $validated[$day . '_room'] = null;
            }
        }

        return $validated;
    }

    public function createTemplate(array $validated): WeekTemplate
    {
        return WeekTemplate::create($validated);
    }


    public function deleteTemplate(WeekTemplate $template): void
    {
        $template->delete();
    }


    public function getApplyFormData(?int $templateId): array
    {
        $templates        = WeekTemplate::all();
        $employees        = Employee::active()->get();
        $selectedTemplate = $templateId ? WeekTemplate::find($templateId) : null;

        return compact('templates', 'employees', 'selectedTemplate');
    }


    public function applyTemplate(array $validated): array
    {
        $template  = WeekTemplate::findOrFail($validated['template_id']);
        $startDate = Carbon::parse($validated['start_date']);
        $endDate   = $startDate->copy()->endOfWeek(Carbon::SUNDAY);

        $absentEmployeeIds = $this->getAbsentEmployeeIds($startDate, $endDate);

        if ($validated['department_target']) {
            return $this->applyToDepartment($validated, $template, $startDate, $absentEmployeeIds);
        }

        return $this->applyToEmployee($validated, $template, $startDate, $absentEmployeeIds);
    }


    private function getAbsentEmployeeIds(Carbon $startDate, Carbon $endDate): array
    {
        return Absence::where('status', 'approved')
            ->where(function ($q) use ($startDate, $endDate) {
                $q->whereBetween('start_date', [$startDate, $endDate])
                  ->orWhereBetween('end_date',   [$startDate, $endDate])
                  ->orWhere(function ($q2) use ($startDate, $endDate) {
                      $q2->where('start_date', '<=', $startDate)
                         ->where('end_date',   '>=', $endDate);
                  });
            })
            ->pluck('employee_id')
            ->unique()
            ->toArray();
    }

    private function applyToDepartment(array $validated, WeekTemplate $template, Carbon $startDate, array $absentEmployeeIds): array
    {
        DB::transaction(function () use ($validated, $template, $startDate, $absentEmployeeIds) {
            // ── Application par département : ignorer les absents ────────
            $employees = Employee::where('department', $validated['department_target'])
                ->active()
                ->whereNotIn('id', $absentEmployeeIds)   // ← EXCLURE LES ABSENTS
                ->get();

            foreach ($employees as $employee) {
                $template->applyToEmployee($employee->id, $startDate);
            }
        });

        // Recalculer le compte (hors absents) pour le message
        $appliedCount = Employee::where('department', $validated['department_target'])
            ->active()
            ->whereNotIn('id', $absentEmployeeIds)
            ->count();

        $skippedCount = count($absentEmployeeIds);

        $message = "Semaine type appliquée à **{$appliedCount} employé(s)** du département {$validated['department_target']}.";
        if ($skippedCount > 0) {
            $message .= " {$skippedCount} employé(s) absent(s) ignoré(s).";
        }

        return ['level' => 'success', 'message' => $message];
    }

    private function applyToEmployee(array $validated, WeekTemplate $template, Carbon $startDate, array $absentEmployeeIds): array
    {
        DB::transaction(function () use ($validated, $template, $startDate, $absentEmployeeIds) {
            // ── Application individuelle : bloquer si absent ─────────────
            if (in_array($validated['employee_id'], $absentEmployeeIds)) {
                // On lève une exception pour rollback et message d'erreur
                throw new \RuntimeException('absent');
            }
            $template->applyToEmployee($validated['employee_id'], $startDate);
        });

        // Cas individuel : vérifier si on a été bloqué (l'exception est catchée
        // par Laravel qui redirige automatiquement, mais on peut aussi gérer ici)
        $employee = Employee::findOrFail($validated['employee_id']);

        if (in_array($validated['employee_id'], $absentEmployeeIds)) {
            return [
                'level'   => 'warning',
                'message' => "{$employee->full_name} est absent(e) sur cette période — la semaine type n'a pas été appliquée.",
            ];
        }

        return ['level' => 'success', 'message' => 'Semaine type appliquée à ' . $employee->full_name];
    }
}
