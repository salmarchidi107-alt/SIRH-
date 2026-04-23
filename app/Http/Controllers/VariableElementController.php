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
        $year = (int) $request->get('year', now()->year);

        $employees = Employee::with(['variableElements' => function ($query) use ($month, $year) {
            $query->where('month', $month)->where('year', $year);
        }])->active()->get();

        $variableElements = VariableElement::where('month', $month)
            ->where('year', $year)
            ->with('employee')
            ->latest()
            ->paginate(20);

        return view('variable-elements.index', compact('variableElements', 'employees', 'month', 'year'));
    }
}

