<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use Illuminate\Support\Facades\Auth;

class ProfileController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        // If user is admin/RH, show their own profile or redirect to employee list
        if ($user->isAdmin() || $user->isRh()) {
            // Try to find linked employee
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
            
            // If no employee found, show dashboard
            return redirect()->route('dashboard');
        }

        // Employee - try to find employee by email or linked employee_id
        $employee = null;

        // First, check if user has a linked employee_id
        if ($user->employee_id) {
            $employee = Employee::find($user->employee_id);
        }

        // If no linked employee, try to find by email
        if (!$employee && $user->email) {
            $employee = Employee::where('email', $user->email)->first();
        }

        // If still no employee found, show error
        if (!$employee) {
            return redirect()->route('dashboard')->with('error', 'Aucun profil employé associé à ce compte. Veuillez contacter l\'administrateur.');
        }

        return view('employees.profile', ['employee' => $employee]);
    }
}

