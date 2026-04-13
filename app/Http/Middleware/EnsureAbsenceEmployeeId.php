<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAbsenceEmployeeId
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            abort(401, 'Non authentifié.');
        }

        if ($user->isEmployee()) {
            if (! $user->employee_id) {
                abort(403, 'Utilisateur employé non lié à un salarié.');
            }

            $request->merge(['employee_id' => $user->employee_id]);
        }

        return $next($request);
    }
}
