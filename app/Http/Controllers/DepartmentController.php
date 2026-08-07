<?php

namespace App\Http\Controllers;

use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DepartmentController extends Controller
{
    public function store(Request $request)
    {
        $tenantId = Auth::user()->tenant_id;

        $request->validate([
            'name'        => 'required|string|max:255|unique:departments,name,NULL,id,tenant_id,' . $tenantId,
            'color'       => 'nullable|string|max:7',
            'chef'        => 'nullable|string|max:255',
            'description' => 'nullable|string|max:500',
        ], [
            'name.unique' => 'Un département avec ce nom existe déjà.',
        ]);

        Department::create([
            'name'        => $request->name,
            'tenant_id' => Auth::user()->tenant_id,
            //'code'        => $request->code ? strtoupper($request->code) : null,
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
        $tenantId = Auth::user()->tenant_id;

        $request->validate([
            'name'        => 'required|string|max:255|unique:departments,name,' . $department->id . ',id,tenant_id,' . $tenantId,
            //'code'        => 'nullable|string|max:10',
            'color'       => 'nullable|string|max:7',
            'chef'        => 'nullable|string|max:255',
            'description' => 'nullable|string|max:500',
        ]);

        $department->update([
            'name'        => $request->name,
          //  'code'        => $request->code ? strtoupper($request->code) : null,
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

        if ($department->rooms()->exists()) {
            $count = $department->rooms()->count();
            return redirect()
                ->route('parametrage.index', ['tab' => 'departments'])
                ->with('error', 'Impossible de supprimer le département « ' . $name . ' » : '
                    . $count . ' salle(s) y sont rattachées. Réaffectez-les d\'abord.');
        }

        $department->delete();

        return redirect()
            ->route('parametrage.index', ['tab' => 'departments'])
            ->with('success', 'Département « ' . $name . ' » supprimé.');
    }
}
