<?php

namespace App\Http\Controllers;

use App\Models\Room;
use App\Models\Department;
use Illuminate\Http\Request;

class RoomController extends Controller
{
    public function index()
    {
        $rooms = Room::with('department')->get();
        $departments = Department::all();

        return view('rooms.index', compact('rooms', 'departments'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'department_id' => 'required'
        ]);

        Room::create([
            'name' => $request->name,
            'department_id' => $request->department_id
        ]);

        return back();
    }
}