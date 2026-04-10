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
->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'auth' => \Illuminate\Auth\Middleware\Authenticate::class,
            'role' => \App\Http\Middleware\CheckRole::class,  
            'badge.auth' => \App\Http\Middleware\BadgeGuard::class,     
                 'ensure.absence.employee' => \App\Http\Middleware\EnsureAbsenceEmployeeId::class,        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
