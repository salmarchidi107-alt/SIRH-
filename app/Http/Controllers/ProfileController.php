<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use Illuminate\Support\Facades\Auth;

class ProfileController extends Controller
{
public function index()
    {
        $user = Auth::user();

    $employee = $user->employee ?? Employee::where('email', $user->email)->first();
    if (!$employee) {
        return redirect()->route('dashboard')->with('error', 'Profil employé non trouvé.');
    }

        return view('employees.show', compact('employee'));
    }
}

