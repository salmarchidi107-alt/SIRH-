<?php

namespace App\Http\Controllers;

use App\Models\WeekTemplate;
use App\Models\Employee;
use App\Models\Absence;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\Room;
use Illuminate\Support\Facades\DB;

class WeekTemplateController extends Controller
{
    public function index()
    {
        $templates = WeekTemplate::all();
        return view('planning.templates.index', compact('templates'));
    }

    public function create()
    {
        $rooms = Room::all();
        return view('planning.templates.create', compact('rooms'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'                 => 'required|string|max:255',
            'department'           => 'nullable|string|max:255',
            'monday_shift_type'    => 'nullable|string',
            'monday_start'         => 'nullable|date_format:H:i',
            'monday_end'           => 'nullable|date_format:H:i',
            'monday_room'          => 'nullable|exists:rooms,id',
            'tuesday_shift_type'   => 'nullable|string',
            'tuesday_start'        => 'nullable|date_format:H:i',
            'tuesday_end'          => 'nullable|date_format:H:i',
            'tuesday_room'         => 'nullable|exists:rooms,id',
            'wednesday_shift_type' => 'nullable|string',
            'wednesday_start'      => 'nullable|date_format:H:i',
            'wednesday_end'        => 'nullable|date_format:H:i',
            'wednesday_room'       => 'nullable|exists:rooms,id',
            'thursday_shift_type'  => 'nullable|string',
            'thursday_start'       => 'nullable|date_format:H:i',
            'thursday_end'         => 'nullable|date_format:H:i',
            'thursday_room'        => 'nullable|exists:rooms,id',
            'friday_shift_type'    => 'nullable|string',
            'friday_start'         => 'nullable|date_format:H:i',
            'friday_end'           => 'nullable|date_format:H:i',
            'friday_room'          => 'nullable|exists:rooms,id',
            'saturday_shift_type'  => 'nullable|string',
            'saturday_start'       => 'nullable|date_format:H:i',
            'saturday_end'         => 'nullable|date_format:H:i',
            'saturday_room'        => 'nullable|exists:rooms,id',
            'sunday_shift_type'    => 'nullable|string',
            'sunday_start'         => 'nullable|date_format:H:i',
            'sunday_end'           => 'nullable|date_format:H:i',
            'sunday_room'          => 'nullable|exists:rooms,id',
        ]);

        // Convert room IDs to room names
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

        WeekTemplate::create($validated);

        return redirect()->route('planning.templates.index')
            ->with('success', 'Semaine type créée avec succès.');
    }

    public function destroy(WeekTemplate $template)
    {
        $template->delete();
        return redirect()->route('planning.templates.index')
            ->with('success', 'Semaine type supprimée.');
    }

    public function applyForm()
    {
        $templates        = WeekTemplate::all();
        $employees        = Employee::active()->get();
        $selectedTemplate = request('template_id')
            ? WeekTemplate::find(request('template_id'))
            : null;

        return view('planning.templates.apply', compact('templates', 'employees', 'selectedTemplate'));
    }

    public function apply(Request $request)
    {
        $validated = $request->validate([
            'template_id'       => 'required|exists:week_templates,id',
            'employee_id'       => 'nullable|exists:employees,id',
            'department_target' => 'nullable|string',
            'start_date'        => 'required|date',
        ]);

        $template  = WeekTemplate::findOrFail($validated['template_id']);
        $startDate = Carbon::parse($validated['start_date']);
        $endDate   = $startDate->copy()->endOfWeek(Carbon::SUNDAY);

        // ── Récupérer les IDs des employés absents sur la semaine cible ──────
        // Un employé est considéré absent si une absence APPROUVÉE chevauche
        // au moins un jour de la semaine à appliquer.
        $absentEmployeeIds = Absence::where('status', 'approved')
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

        DB::transaction(function () use ($validated, $template, $startDate, $absentEmployeeIds) {

            if ($validated['department_target']) {
                // ── Application par département : ignorer les absents ────────
                $employees = Employee::where('department', $validated['department_target'])
                    ->active()
                    ->whereNotIn('id', $absentEmployeeIds)   // ← EXCLURE LES ABSENTS
                    ->get();

                foreach ($employees as $employee) {
                    $template->applyToEmployee($employee->id, $startDate);
                }

            } else {
                // ── Application individuelle : bloquer si absent ─────────────
                if (in_array($validated['employee_id'], $absentEmployeeIds)) {
                    // On lève une exception pour rollback et message d'erreur
                    throw new \RuntimeException('absent');
                }
                $template->applyToEmployee($validated['employee_id'], $startDate);
            }
        });

        // ── Messages de retour ───────────────────────────────────────────────
        if ($validated['department_target']) {
            // Recalculer le compte (hors absents) pour le message
            $appliedCount = Employee::where('department', $validated['department_target'])
                ->active()
                ->whereNotIn('id', $absentEmployeeIds)
                ->count();

            $skippedCount = count($absentEmployeeIds);

            $msg = "Semaine type appliquée à **{$appliedCount} employé(s)** du département {$validated['department_target']}.";
            if ($skippedCount > 0) {
                $msg .= " {$skippedCount} employé(s) absent(s) ignoré(s).";
            }

            return back()->with('success', $msg);

        } else {
            // Cas individuel : vérifier si on a été bloqué (l'exception est catchée
            // par Laravel qui redirige automatiquement, mais on peut aussi gérer ici)
            $employee = Employee::findOrFail($validated['employee_id']);

            if (in_array($validated['employee_id'], $absentEmployeeIds)) {
                return back()->with('warning',
                    "{$employee->full_name} est absent(e) sur cette période — la semaine type n'a pas été appliquée."
                );
            }

            return back()->with('success', 'Semaine type appliquée à ' . $employee->full_name);
        }
    }
}
