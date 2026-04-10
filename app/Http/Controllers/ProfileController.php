<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use Illuminate\Support\Facades\Auth;

class ProfileController extends Controller
{
public function index()
    {
        $user = Auth::user();

        if (!$user->employee_id && !$user->email) {
            return redirect()->route('dashboard')->with('error', 'Aucun profil employé associé à ce compte.');
        }

        $employee = Employee::where('id', $user->employee_id)
            ->orWhere('email', $user->email)
            ->first();

        if (!$employee) {
            return redirect()->route('dashboard')->with('error', 'Profil employé non trouvé.');
        }

        return view('employees.show', compact('employee'));
    }
}

