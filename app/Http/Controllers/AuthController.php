<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function showRegisterForm()
    {
        return view('auth.register');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();
            
            
            $user = Auth::user();
            
            
            if (!$user->role) {
                $user->role = User::ROLE_EMPLOYEE;
                $user->save();
            }
            
            
            if (!$user->employee_id) {
                $employee = Employee::where('email', $user->email)->first();
                if ($employee) {
                    $employee->user_id = $user->id;
                    $employee->save();
                    $user->employee_id = $employee->id;
                    $user->save();
                }
            }
            
            return redirect()->intended('/')->with('success', 'Connexion réussie! Bienvenue ' . $user->name);
        }

        return back()->withErrors([
            'email' => 'Les identifiants fournis ne correspondent pas à nos enregistrements.',
        ])->withInput($request->except('password'));
    }

    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
        ]);

        
        $employee = Employee::where('email', $validated['email'])->first();
        
        
        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => $employee ? User::ROLE_EMPLOYEE : User::ROLE_EMPLOYEE,
            'employee_id' => $employee ? $employee->id : null,
        ]);

     
        if ($employee && !$employee->user_id) {
            $employee->user_id = $user->id;
            $employee->save();
        }

        Auth::login($user);

        return redirect('/')->with('success', 'Compte créé avec succès! Bienvenue ' . $user->name);
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login')->with('success', 'Déconnexion réussie!');
    }
}

