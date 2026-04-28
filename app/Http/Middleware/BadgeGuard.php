<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class BadgeGuard
{
    public function handle(Request $request, Closure $next): mixed
    {
        $userId = $request->session()->get('badge_user_id');

        if (!$userId) {
            return redirect()->route('badge.auth.show', ['action' => 'entree']);
        }

        $user = User::find($userId);
        if (!$user) {
            $request->session()->forget('badge_user_id');
            return redirect()->route('badge.auth.show', ['action' => 'entree']);
        }

        // Injecter l'utilisateur dans le guard badge pour que auth('badge')->user() fonctionne
        Auth::guard('badge')->setUser($user);

        return $next($request);
    }
    
}