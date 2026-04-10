<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
 use App\Ai\Agents\AssistantRH;
class AuthController extends Controller
{
   

public function ask(Request $request)
{
    $agent = app(AssistantRH::class);

    $response = $agent->prompt($request->message);

    return response()->json([
        'reply' => $response->text
    ]);
}
    public function showLoginForm()
    {
        return view('auth.login');
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
            
            $defaultRedirect = $user->role === User::ROLE_EMPLOYEE
                ? route('employee.dashboard')
                : route('dashboard');

            return redirect()->intended($defaultRedirect)
                ->with('success', 'Connexion réussie! Bienvenue ' . $user->name);
        }

        return back()->withErrors([
            'email' => 'Les identifiants fournis ne correspondent pas à nos enregistrements.',
        ])->withInput($request->except('password'));
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login')->with('success', 'Déconnexion réussie!');
    }
}

