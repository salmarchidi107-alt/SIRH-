<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware CheckModulePermission
 *
 * Usage : ->middleware('permission:employees,view')
 *
 * - superadmin / admin → accès total sans consultation de la base
 * - rh / employee      → vérifie la table user_permissions
 */
class CheckModulePermission
{
    public function handle(Request $request, Closure $next, string $module, string $action = 'view'): Response
    {
        $user = Auth::user();

        if (! $user) {
            return redirect()->route('login');
        }

        // superadmin et admin → accès total
        if ($user->isFullAccessRole()) {
            return $next($request);
        }

        // rh et employee → vérification en base
        if (! $user->hasPermission($module, $action)) {
            if ($request->expectsJson()) {
                return response()->json([
                    'error'  => 'Accès refusé.',
                    'module' => $module,
                    'action' => $action,
                ], 403);
            }

            $dashboardRoute = in_array($user->role, ['rh'])
                ? 'admin.dashboard'
                : 'employee.dashboard';

            return redirect()
                ->route($dashboardRoute)
                ->with('error', "Vous n'avez pas accès à cette section.");
        }

        return $next($request);
    }
}
