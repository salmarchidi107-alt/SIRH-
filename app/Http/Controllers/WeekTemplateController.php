<?php

namespace App\Http\Controllers;

use App\Models\WeekTemplate;
use App\Models\Employee;
use Illuminate\Http\Request;
use Carbon\Carbon;

class WeekTemplateController extends Controller
{
    public function index()
    {
        $templates = WeekTemplate::all();
        return view('planning.templates.index', compact('templates'));
    }

    public function create()
    {
        return view('planning.templates.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
'name' => 'required|string|max:255',
            'department' => 'nullable|string|max:255',
            'monday_shift_type' => 'nullable|string',
            'monday_start' => 'nullable',
            'monday_end' => 'nullable',
            'tuesday_shift_type' => 'nullable|string',
            'tuesday_start' => 'nullable',
            'tuesday_end' => 'nullable',
            'wednesday_shift_type' => 'nullable|string',
            'wednesday_start' => 'nullable',
            'wednesday_end' => 'nullable',
            'thursday_shift_type' => 'nullable|string',
            'thursday_start' => 'nullable',
            'thursday_end' => 'nullable',
            'friday_shift_type' => 'nullable|string',
            'friday_start' => 'nullable',
            'friday_end' => 'nullable',
            'saturday_shift_type' => 'nullable|string',
            'saturday_start' => 'nullable',
            'saturday_end' => 'nullable',
            'sunday_shift_type' => 'nullable|string',
            'sunday_start' => 'nullable',
            'sunday_end' => 'nullable',
        ]);

        WeekTemplate::create($validated);

        return redirect()->route('planning.templates.index')->with('success', 'Semaine type créée avec succès.');
    }

    public function destroy(WeekTemplate $template)
    {
        $template->delete();
        return redirect()->route('planning.templates.index')->with('success', 'Semaine type supprimée.');
    }

    public function applyForm()
    {
        $templates = WeekTemplate::all();
        $employees = Employee::active()->get();
        return view('planning.templates.apply', compact('templates', 'employees'));
    }

    public function apply(Request $request)
    {
        $validated = $request->validate([
            'template_id' => 'required|exists:week_templates,id',
            'employee_id' => 'nullable|exists:employees,id',
            'department_target' => 'nullable|string',
            'start_date' => 'required|date',
        ]);

        $template = WeekTemplate::findOrFail($validated['template_id']);
        $startDate = Carbon::parse($validated['start_date']);

        if ($validated['department_target']) {
            $employees = Employee::where('department', $validated['department_target'])
                ->active()
                ->get();
            
            foreach ($employees as $employee) {
                $template->applyToEmployee($employee->id, $startDate);
            }

            return back()->with('success', "Semaine type appliquée à **{$employees->count()} employés** du département {$validated['department_target']}");
        } else {
            $employee = Employee::findOrFail($validated['employee_id']);
            $template->applyToEmployee($validated['employee_id'], $startDate);
            return back()->with('success', 'Semaine type appliquée à ' . $employee->full_name);
        }
    }
}
