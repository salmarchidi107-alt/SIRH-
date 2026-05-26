<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware CheckRole
 *
 * Usage : ->middleware('role:admin,rh')
 *
 * CORRECTION : on compare directement $user->role aux rôles autorisés.
 * On ne passe plus par $user->can() qui dépend de config/roles.php
 * et peut retourner false si la config est incomplète.
 *
 * Superadmin passe toujours.
 */
class CheckRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('login');
        }

        // Superadmin passe toujours, quel que soit le rôle demandé
        if ($user->isSuperAdmin()) {
            return $next($request);
        }

        // Comparaison directe du rôle (case-insensitive pour sécurité)
        $userRole     = strtolower(trim($user->role ?? ''));
        $allowedRoles = array_map('strtolower', array_map('trim', $roles));

        if (in_array($userRole, $allowedRoles)) {
            return $next($request);
        }

        if ($request->expectsJson()) {
            return response()->json(['error' => 'Accès non autorisé pour votre rôle.'], 403);
        }

        // Redirection intelligente selon le rôle réel
        $redirect = $user->isAdminOrRh()
            ? route('admin.dashboard')
            : route('employee.dashboard');

        return redirect($redirect)
            ->with('error', 'Vous n\'avez pas accès à cette section.');
    }
}
