<?php

namespace App\Http\Controllers;

use App\Models\VariableElement;
use App\Services\VariableElementService;
use Illuminate\Http\Request;

class VariableElementController extends Controller
{
    public function __construct(private VariableElementService $variableElementService) {}

    public function index(Request $request)
    {
        return view('variable-elements.index', $this->variableElementService->getIndexData($request));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'month'       => 'required|integer|min:1|max:12',
            'year'        => 'required|integer',
            'category'    => 'required|in:gain,retenue',
            'rubrique'    => 'nullable|string|max:100',
            'label'       => 'required|string|max:150',
            'amount'      => 'required|numeric|min:0',
            'unit'        => 'nullable|string|max:20',
        ]);

        $this->variableElementService->createElement($validated);

        return redirect()
            ->route('variables.index', [
                'month' => $validated['month'],
                'year'  => $validated['year'],
            ])
            ->with('success', 'Élément ajouté avec succès.');
    }

    public function destroy(VariableElement $variableElement)
    {
        $this->variableElementService->deleteElement($variableElement);

        return back()->with('success', 'Élément supprimé.');
    }
}
