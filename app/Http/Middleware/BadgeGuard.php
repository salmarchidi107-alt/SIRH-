<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Symfony\Component\HttpFoundation\Response;

class BadgeGuard
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
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

        // Inject badge user into guard so auth('badge')->user() works
        Auth::guard('badge')->setUser($user);

        return $next($request);
    }
}

