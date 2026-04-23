<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\EmployeeController;

 // Routes DEV localhost /employees sans tenant
Route::middleware(['web', 'auth'])->group(function () {
    Route::middleware('admin')->group(function () {
        Route::get('employees', [EmployeeController::class, 'index'])->name('employees.index');
        Route::get('employees/create', [EmployeeController::class, 'create'])->name('employees.create');
        Route::post('employees', [EmployeeController::class, 'store'])->name('employees.store');
        Route::get('employees/export-pdf', [EmployeeController::class, 'exportPdf'])->name('employees.export-pdf');
        Route::get('employees/export-pdf-dept/{department}', [EmployeeController::class, 'exportPdfByDept'])->name('employees.export-pdf-dept');
        Route::resource('employees', EmployeeController::class)->except(['index', 'create', 'store']);
    });
});

