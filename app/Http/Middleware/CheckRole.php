<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Str;

class CheckRole
{
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        $user = $request->user();

        if (! $user) {
            abort(401, 'Non authentifié.');
        }

        $allowedRoles = is_array($roles) ? $roles : func_get_args();

        foreach ($allowedRoles as $role) {
            if (Str::is($role, $user->role) || $user->can($role)) {
                return $next($request);
            }
        }

        abort(403, 'Accès non autorisé pour votre rôle.');
    }
}

