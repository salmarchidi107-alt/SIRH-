<?php

use Illuminate\Support\Facades\Route;

// SuperAdmin Multitenants routes
Route::middleware(['auth', 'superadmin'])->group(function () {
    Route::get('/dashboard', [\App\Http\Controllers\SuperAdmin\DashboardController::class, 'index'])->name('superadmin.dashboard');
    Route::resource('tenants', \App\Http\Controllers\SuperAdmin\TenantController::class);
    Route::post('tenants/{tenant}/suspend', [\App\Http\Controllers\SuperAdmin\TenantController::class, 'suspend'])->name('superadmin.tenants.suspend');
    Route::post('tenants/{tenant}/reactivate', [\App\Http\Controllers\SuperAdmin\TenantController::class, 'reactivate'])->name('superadmin.tenants.reactivate');
    Route::get('/settings', [\App\Http\Controllers\SuperAdmin\SettingsController::class, 'index'])->name('superadmin.settings.index');
    Route::get('/settings/{tab?}', [\App\Http\Controllers\SuperAdmin\SettingsController::class, 'show'])->name('superadmin.settings.show');
    Route::post('/settings', [\App\Http\Controllers\SuperAdmin\SettingsController::class, 'update'])->name('superadmin.settings.update');

    Route::put('/settings/plans/{plan}', [\App\Http\Controllers\SuperAdmin\SettingsController::class, 'updatePlan'])
        ->name('superadmin.settings.plans.update');

    Route::put('/settings/global', [\App\Http\Controllers\SuperAdmin\SettingsController::class, 'updateGlobal'])
        ->name('superadmin.settings.global.update');
    Route::get('/roles', [\App\Http\Controllers\SuperAdmin\RoleController::class, 'index'])->name('superadmin.roles.index');
});

