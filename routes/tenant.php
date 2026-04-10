<?php

use Illuminate\Support\Facades\Route;

// Tenant routes (tenant context)
Route::middleware([
    'web',
    \App\Http\Middleware\DomainTenant::class,
])->group(function () {
    Route::get('/', function () {
        return view('welcome');
    });

    // Tenant dashboard
    Route::get('/dashboard', [\App\Http\Controllers\DashboardController::class, 'index'])->name('tenant.dashboard');

    // Auth
    Route::get('/login', [\App\Http\Controllers\Auth\LoginController::class, 'showLoginForm'])->name('tenant.login');
    Route::post('/login', [\App\Http\Controllers\Auth\LoginController::class, 'login']);
    Route::post('/logout', [\App\Http\Controllers\Auth\LoginController::class, 'logout'])->name('tenant.logout');

    // Employee (DISABLED - use web.php protected routes)
    // Route::resource('employees', \App\Http\Controllers\EmployeeController::class);

    // Absences (DISABLED - use web.php tenant-user/admin)
    // Route::resource('absences', \App\Http\Controllers\AbsenceController::class);

    // Salary (DISABLED - admin only)
    // Route::resource('salaries', \App\Http\Controllers\SalaryController::class);

    // Planning (DISABLED - use web.php tenant-user/admin)
    // Route::resource('planning', \App\Http\Controllers\PlanningController::class);

    // Pointage (DISABLED - admin only)
    // Route::resource('pointages', \App\Http\Controllers\PointageController::class);

    // API routes for scan
    Route::post('/api/pointage/scan', [\App\Http\Controllers\API\PointageScanController::class, 'scan']);
});

// Note: Add your existing tenant routes here or include tenant-routes.php if exists

