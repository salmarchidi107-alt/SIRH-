<?php

namespace App\Http\Controllers;

use App\Models\Room;
use App\Models\Department;
use Illuminate\Http\Request;

class RoomController extends Controller
{
    public function index()
    {
        $rooms       = Room::with('department')->orderBy('name')->get();
        $departments = Department::orderBy('name')->get();

        return view('rooms.index', compact('rooms', 'departments'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'          => 'required|string|max:255',
            'department_id' => 'required|exists:departments,id',
            'capacity'      => 'nullable|integer|min:1',
            'description'   => 'nullable|string|max:500',
        ]);

        Room::create([
            'name'          => $request->name,
            'department_id' => $request->department_id,
            'capacity'      => $request->capacity,
            'description'   => $request->description,
        ]);

        return redirect()
            ->route('parametrage.index', ['tab' => 'rooms'])
            ->with('success', 'Salle « ' . $request->name . ' » créée avec succès.');
    }

    public function update(Request $request, Room $room)
    {
        $request->validate([
            'name'          => 'required|string|max:255',
            'department_id' => 'required|exists:departments,id',
            'capacity'      => 'nullable|integer|min:1',
            'description'   => 'nullable|string|max:500',
        ]);

        $room->update([
            'name'          => $request->name,
            'department_id' => $request->department_id,
            'capacity'      => $request->capacity,
            'description'   => $request->description,
        ]);

        return redirect()
            ->route('parametrage.index', ['tab' => 'rooms'])
            ->with('success', 'Salle « ' . $room->name . ' » mise à jour.');
    }

    public function destroy(Room $room)
    {
        $name = $room->name;
        $room->delete();

        return redirect()
            ->route('parametrage.index', ['tab' => 'rooms'])
            ->with('success', 'Salle « ' . $name . ' » supprimée.');
    }
}