<?php

namespace App\Http\Controllers;

use App\Models\VariableElement;
use App\Models\Employee;
use Illuminate\Http\Request;

class VariableElementController extends Controller
{
    public function index(Request $request)
    {
        $month = (int) $request->get('month', now()->month);
        $year  = (int) $request->get('year',  now()->year);

        $employees = Employee::with([
            'variableElements' => function ($query) use ($month, $year) {
                $query->where('month', $month)->where('year', $year);
            }
        ])->active()->get();

        $variableElements = VariableElement::where('month', $month)
            ->where('year', $year)
            ->with('employee')
            ->latest()
            ->paginate(100);

        return view('variable-elements.index', [
            'elements'  => $variableElements,
            'employees' => $employees,
            'month'     => $month,
            'year'      => $year,
        ]);
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

        VariableElement::create([
            'employee_id' => $validated['employee_id'],
            'month'       => $validated['month'],
            'year'        => $validated['year'],
            'type'        => $validated['category'], // ✅ 'gain' ou 'retenue' — valeurs valides de l'enum
            'rubrique'    => $validated['rubrique'] ?? null,
            'label'       => $validated['label'],
            'amount'      => $validated['amount'],
            'unit'        => $validated['unit'] ?? 'MAD',
        ]);

        return redirect()
            ->route('variables.index', [
                'month' => $validated['month'],
                'year'  => $validated['year'],
            ])
            ->with('success', 'Élément ajouté avec succès.');
    }

    public function destroy(VariableElement $variableElement)
    {
        $variableElement->delete();

        return back()->with('success', 'Élément supprimé.');
    }
}
