<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\VariableElement;
use Illuminate\Http\Request;

class VariableElementController extends Controller
{
    public function index(Request $request)
    {
        $month     = (int) $request->get('month', now()->month);
        $year      = (int) $request->get('year',  now()->year);
        $elements  = VariableElement::with('employee')
            ->where('month', $month)->where('year', $year)
            ->orderBy('employee_id')->get();
        $employees = Employee::orderByRaw("CONCAT(first_name, ' ', last_name) ASC")->get();

        return view('salary.variables', compact('elements', 'employees', 'month', 'year'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'month'       => 'required|integer|min:1|max:12',
            'year'        => 'required|integer|min:2000',
            'category'    => 'required|in:gain,retenue',
            'rubrique'    => 'required|string',
            'label'       => 'required|string|max:150',
            'amount'      => 'required|numeric|min:0',
            'unit'        => 'nullable|string',
        ]);

        $data = $request->all();
        $data['type'] = $data['category'];
        VariableElement::create($data);
        return back()->with('success', 'Élément variable ajouté.');
    }

    public function destroy(VariableElement $variableElement)
    {
        $variableElement->delete();
        return back()->with('success', 'Élément supprimé.');
    }
}

