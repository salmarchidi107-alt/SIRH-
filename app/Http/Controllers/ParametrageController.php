<?php

namespace App\Http\Controllers;

use App\Models\Room;
use App\Models\Department;
use Illuminate\Http\Request;

class ParametrageController extends Controller
{
    // ── Page principale ─────────────────────────────────────────────────────
    public function index()
    {
        $rooms       = Room::with('department')->orderBy('name')->get();
        $departments = Department::withCount('rooms')->orderBy('name')->get();

        return view('parametrage.index', compact('rooms', 'departments'));
    }
}