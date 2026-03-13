<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use Illuminate\Support\Facades\Auth;

class ProfileController extends Controller
{
    public function index()
    {
        $user = Auth::user();

       
        if ($user->isAdmin() || $user->isRh()) {
          
            $employee = null;
            if ($user->employee_id) {
                $employee = Employee::find($user->employee_id);
            }
            if (!$employee && $user->email) {
                $employee = Employee::where('email', $user->email)->first();
            }
            
            if ($employee) {
                return view('employees.profile', ['employee' => $employee]);
            }
            
          
            return redirect()->route('dashboard');
        }

        $employee = null;

        
        if ($user->employee_id) {
            $employee = Employee::find($user->employee_id);
        }

     
        if (!$employee && $user->email) {
            $employee = Employee::where('email', $user->email)->first();
        }

        
        if (!$employee) {
            return redirect()->route('dashboard')->with('error', 'Aucun profil employé associé à ce compte. Veuillez contacter l\'administrateur.');
        }

        return view('employees.profile', ['employee' => $employee]);
    }
}

