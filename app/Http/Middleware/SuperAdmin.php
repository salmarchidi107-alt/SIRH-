<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SuperAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        $user = auth()->user();
        if (!($user->role === 'superadmin' || is_null($user->tenant_id))) {
            \Illuminate\Support\Facades\Log::warning("SuperAdmin access denied for user {$user->email} (role={$user->role}, tenant_id=" . ($user->tenant_id ?? 'NULL') . ')');
            auth()->logout();
            return redirect()->route('login')->withErrors(['email' => 'Accès réservé au Super Admin.']);
        }

        return $next($request);
    }

}
