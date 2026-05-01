<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'identify-tenant' => \App\Http\Middleware\IdentifyTenant::class,
            'admin'           => \App\Http\Middleware\Admin::class,
            'employee'        => \App\Http\Middleware\Employee::class,
            'superadmin'      => \App\Http\Middleware\SuperAdmin::class,
            'domain-tenant'   => \App\Http\Middleware\DomainTenant::class,
            'tenant-user'     => \App\Http\Middleware\Authenticated::class,
            'badge.auth'      => \App\Http\Middleware\BadgeGuard::class,
            'role'            => \App\Http\Middleware\CheckRole::class,
            'ensure.absence.employee' => \App\Http\Middleware\EnsureAbsenceEmployeeId::class,
        ]);

    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })
    ->create();
