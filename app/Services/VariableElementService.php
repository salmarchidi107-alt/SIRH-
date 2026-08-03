<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\VariableElement;
use Illuminate\Http\Request;

class VariableElementService
{
    public function getIndexData(Request $request): array
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
            ->with(['employee' => function ($query) {
                // withTrashed() : on garde l'affichage même si l'employé
                // a été supprimé (soft delete) entre-temps, pour éviter
                // "Attempt to read property full_name on null".
                if (method_exists($query->getModel(), 'trashed')) {
                    $query->withTrashed();
                }
            }])
            ->latest()
            ->paginate(100);

        return [
            'elements'  => $variableElements,
            'employees' => $employees,
            'month'     => $month,
            'year'      => $year,
        ];
    }

    public function createElement(array $validated): VariableElement
    {
        return VariableElement::create([
            'employee_id' => $validated['employee_id'],
            'month'       => $validated['month'],
            'year'        => $validated['year'],
            'type'        => $validated['category'], // 'gain' ou 'retenue' — valeurs valides de l'enum
            'rubrique'    => $validated['rubrique'] ?? null,
            'label'       => $validated['label'],
            'amount'      => $validated['amount'],
            'unit'        => $validated['unit'] ?? 'MAD',
        ]);
    }

    public function deleteElement(VariableElement $variableElement): void
    {
        $variableElement->delete();
    }
}
