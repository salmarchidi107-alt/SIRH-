<?php
// app/Http/Controllers/Badge/BadgeAuthController.php

namespace App\Http\Controllers\Badge;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Http\Controllers\Badge\BadgePointageController;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class BadgeAuthController extends Controller
{
    // ── Afficher la page d'authentification avec action ─────────────────────
    public function showAuth(Request $request)
    {
        return view('badge.login', [
            'action' => $request->action ?? 'entree',
            'intent' => $request->intent ?? $request->action ?? 'entree'
        ]);
    }

    // ── Auth + action (entree/sortie) + signature ──────────────────────────
    public function authAction(Request $request)
{
    
    $action = $request->input('action', 'entree');

    $request->validate([
        'pin'       => 'required|string|size:6|regex:/^[0-9]{4}[A-Z]{2}$/',
        'signature' => 'required|string',
    ]);

    $employees = Employee::where('status', 'active')->get();
    $employee  = $employees->first(function ($emp) use ($request) {
        if (empty($emp->pin)) return false;
        try {
            if (Hash::check($request->pin, $emp->pin)) return true;
        } catch (\Exception $e) {}
        return $emp->pin === $request->pin;
    });

    if (!$employee) {
        return back()->withErrors(['pin' => 'PIN incorrect.'])->withInput();
    }

    // Auto-create user si besoin
    $user = $employee->user;
    if (!$user) {
        $email = $employee->email ?: ('badge.emp' . $employee->id . '@hospitalrh.local');
        $user  = User::firstOrCreate(
            ['email' => $email],
            [
                'name'     => trim(($employee->first_name ?? '') . ' ' . ($employee->last_name ?? '')) ?: 'Employee ' . $employee->id,
                'password' => Hash::make(Str::random(16)),
                'role'     => User::ROLE_EMPLOYEE,
            ]
        );
        $employee->user_id = $user->id;
        $employee->save();
        $user->employee_id = $employee->id;
        $user->save();
    }

    // Sauvegarder la signature
    $employee->update(['signature' => substr($request->signature, 0, 255)]);

    //  Seul mécanisme d'auth badge : la session
    $request->session()->put('badge_user_id', $user->id);
    $request->session()->save();

    // TODO : separate entree pause fro, debut de shift 
    // Enregistrer le pointage
   $subaction  = $request->input('action_sub', $action);

$recordType = match ($subaction) {
    'debut'         => 'entree',         // début shift
    'sortie_pause'  => 'pause',          // début pause
    'retour_pause'  => 'retour_pause',   // fin pause (séparé)
    'fin_shift'     => 'sortie',         // fin shift
    default         => $action === 'entree' ? 'entree' : 'sortie',
};

    try {
        app(BadgePointageController::class)->recordAction($recordType, $employee);
        // dd("selma zeee");
        $request->session()->put('last_type', $recordType);
        $request->session()->save();
    } catch (\Exception $e) {
        Log::error('Badge pointage error', ['error' => $e->getMessage(), 'employee' => $employee->id]);
        dd($e );
    }

    return redirect()->route('badge.result');
}

    // ── Déconnexion ───────────────────────────────────────────────────────
   public function logout(Request $request)
{
    $request->session()->forget('badge_user_id');
    $request->session()->forget('last_type');
    Auth::guard('badge')->logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();

    return redirect()->route('badge.pointage');
}
}

