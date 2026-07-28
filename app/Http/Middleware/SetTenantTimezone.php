<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SetTenantTimezone
{
    public function handle(Request $request, Closure $next)
    {
        $tenant = $request->user()?->tenant; // adaptez selon votre relation user->tenant

        if ($tenant && $tenant->timezone) {
            // N'écrit rien dans config/app.php — override en mémoire,
            // valable uniquement pour la durée de cette requête.
            config(['app.timezone' => $tenant->timezone]);
            date_default_timezone_set($tenant->timezone);
        }

        return $next($request);
    }
}
