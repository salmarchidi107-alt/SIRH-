<?php

namespace App\Http\Controllers;

use App\Models\Department;
use Illuminate\Http\Request;

class DepartmentController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'name'        => 'required|string|max:255|unique:departments,name',
            'code'        => 'nullable|string|max:10',
            'color'       => 'nullable|string|max:7',
            'chef'        => 'nullable|string|max:255',
            'description' => 'nullable|string|max:500',
        ], [
            'name.unique' => 'Un département avec ce nom existe déjà.',
        ]);

        Department::create([
            'name'        => $request->name,
            'code'        => $request->code ? strtoupper($request->code) : null,
            'color'       => $request->color ?? '#0ea5e9',
            'chef'        => $request->chef,
            'description' => $request->description,
        ]);

        return redirect()
            ->route('parametrage.index', ['tab' => 'departments'])
            ->with('success', 'Département « ' . $request->name . ' » créé avec succès.');
    }

    public function update(Request $request, Department $department)
    {
        $request->validate([
            'name'        => 'required|string|max:255|unique:departments,name,' . $department->id,
            'code'        => 'nullable|string|max:10',
            'color'       => 'nullable|string|max:7',
            'chef'        => 'nullable|string|max:255',
            'description' => 'nullable|string|max:500',
        ]);

        $department->update([
            'name'        => $request->name,
            'code'        => $request->code ? strtoupper($request->code) : null,
            'color'       => $request->color ?? $department->color,
            'chef'        => $request->chef,
            'description' => $request->description,
        ]);

        return redirect()
            ->route('parametrage.index', ['tab' => 'departments'])
            ->with('success', 'Département « ' . $department->name . ' » mis à jour.');
    }

    public function destroy(Department $department)
    {
        $name = $department->name;

        // Désassocier les salles avant suppression
        $department->rooms()->update(['department_id' => null]);
        $department->delete();

        return redirect()
            ->route('parametrage.index', ['tab' => 'departments'])
            ->with('success', 'Département « ' . $name . ' » supprimé. Les salles ont été désassociées.');
    }
}