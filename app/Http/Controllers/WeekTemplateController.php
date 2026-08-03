<?php

namespace App\Http\Controllers;

use App\Models\WeekTemplate;
use App\Services\WeekTemplateService;
use Illuminate\Http\Request;

class WeekTemplateController extends Controller
{
    public function __construct(private WeekTemplateService $weekTemplateService) {}

    public function index()
    {
        return view('planning.templates.index', $this->weekTemplateService->getIndexData());
    }

    public function create()
    {
        return view('planning.templates.create', $this->weekTemplateService->getCreateData());
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

        $validated = $this->weekTemplateService->resolveRoomNames($validated);

        $this->weekTemplateService->createTemplate($validated);

        return redirect()->route('planning.templates.index')
            ->with('success', 'Semaine type créée avec succès.');
    }

    public function destroy(WeekTemplate $template)
    {
        $this->weekTemplateService->deleteTemplate($template);

        return redirect()->route('planning.templates.index')
            ->with('success', 'Semaine type supprimée.');
    }

    public function applyForm()
    {
        $data = $this->weekTemplateService->getApplyFormData(request('template_id'));

        return view('planning.templates.apply', $data);
    }

    public function apply(Request $request)
    {
        $validated = $request->validate([
            'template_id'       => 'required|exists:week_templates,id',
            'employee_id'       => 'nullable|exists:employees,id',
            'department_target' => 'nullable|string',
            'start_date'        => 'required|date',
        ]);

        $result = $this->weekTemplateService->applyTemplate($validated);

        return back()->with($result['level'], $result['message']);
    }
}
