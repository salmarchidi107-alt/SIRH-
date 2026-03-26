<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\PlanningController;
use App\Http\Controllers\AbsenceController;
use App\Http\Controllers\SalaryController;
use App\Http\Controllers\TrombinoscopeController;
use App\Http\Controllers\NewsController;
use App\Http\Controllers\WeekTemplateController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\VueEnsembleController;
use App\Http\Controllers\VariableElementController;
use App\Http\Controllers\PayrollSettingController;
use App\Models\User;
use App\Models\Employee;

// Public routes
Route::get('/dashboard', [DashboardController::class, 'dashboard'])->middleware('auth');
Route::get('/actualites', [NewsController::class, 'index']);
Route::get('/holidays/debug', [App\Http\Controllers\HolidayController::class, 'debug']);
Route::get('/holidays/{year}/{month}', [App\Http\Controllers\HolidayController::class, 'index']);
Route::get('/temps/vue-ensemble', [VueEnsembleController::class, 'index'])->name('temps.vue-ensemble');

// Link users utility
Route::get('/link-users', function () {
    $linked = 0;
    foreach (User::all() as $user) {
        $employee = Employee::where('email', $user->email)->first();
        if ($employee && !$employee->user_id) {
            $employee->user_id = $user->id;
            $employee->save();
            $linked++;
            echo "Linked: {$user->email} -> {$employee->first_name} {$employee->last_name}<br>";
        }
    }
    echo $linked > 0 ? "Linked {$linked} users!" : "No new links.";
});

// Auth routes
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Auth protected
Route::middleware(['auth'])->group(function () {
    Route::get('/', fn() => redirect()->route('dashboard'));

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/profile', [ProfileController::class, 'index'])->name('profile');
    
    Route::resource('news', NewsController::class);
    
    // Employees
    Route::get('/employees/export', [EmployeeController::class, 'export'])
    ->name('employees.export');

Route::resource('employees', EmployeeController::class);
    
    // Trombinoscope
    Route::get('/trombinoscope', [TrombinoscopeController::class, 'index'])->name('trombinoscope');
    Route::get('/trombinoscope/export', [TrombinoscopeController::class, 'export'])->name('trombinoscope.export');
    
    // Planning
    Route::get('/planning/weekly', [PlanningController::class, 'weekly'])->name('planning.weekly');
    Route::get('/planning/weekly/export', [PlanningController::class, 'exportWeekly'])->name('planning.weekly.export');
    Route::get('/planning/global', [PlanningController::class, 'global'])->name('planning.global');
    Route::get('/planning/monthly', [PlanningController::class, 'monthly'])->name('planning.monthly');
    Route::get('/planning/monthly/export', [PlanningController::class, 'exportMonthly'])->name('planning.monthly.export');
    Route::get('/planning/show/{employee?}', fn() => redirect()->route('planning.weekly'))->name('planning.show');
    Route::post('/planning', [PlanningController::class, 'store'])->name('planning.store');
    Route::put('/planning/{planning}', [PlanningController::class, 'update'])->name('planning.update');
    Route::delete('/planning/{planning}', [PlanningController::class, 'destroy'])->name('planning.destroy');
    Route::post('/planning/drag-drop', [PlanningController::class, 'updateDragDrop'])->name('planning.dragDrop');
    
    // Week templates
    Route::get('/planning/templates', [WeekTemplateController::class, 'index'])->name('planning.templates.index');
    Route::get('/planning/templates/create', [WeekTemplateController::class, 'create'])->name('planning.templates.create');
    Route::post('/planning/templates', [WeekTemplateController::class, 'store'])->name('planning.templates.store');
    Route::delete('/planning/templates/{template}', [WeekTemplateController::class, 'destroy'])->name('planning.templates.destroy');
    Route::get('/planning/templates/apply', [WeekTemplateController::class, 'applyForm'])->name('planning.templates.apply.form');
    Route::post('/planning/templates/apply', [WeekTemplateController::class, 'apply'])->name('planning.templates.apply');
    Route::delete('/planning/{planning}', [PlanningController::class, 'destroy'])->name('planning.destroy');
    // Absences
    Route::prefix('absences')->name('absences.')->group(function () {
        Route::get('/', [AbsenceController::class, 'index'])->name('index');
        Route::get('/create', [AbsenceController::class, 'create'])->name('create');
        Route::post('/', [AbsenceController::class, 'store'])->name('store');
        Route::get('/calendar', [AbsenceController::class, 'calendar'])->name('calendar');
        Route::get('/counters', [AbsenceController::class, 'counters'])->name('counters');
        Route::get('/counters/export', [AbsenceController::class, 'countersExport'])->name('counters.export');
        Route::get('/droits/export', [AbsenceController::class, 'droitsExport'])->name('droits.export');
        Route::get('/export', [AbsenceController::class, 'export'])->name('export');
        Route::get('/{absence}', [AbsenceController::class, 'show'])->name('show');
        Route::get('/{absence}/edit', [AbsenceController::class, 'edit'])->name('edit');
        Route::put('/{absence}', [AbsenceController::class, 'update'])->name('update');
        Route::delete('/{absence}', [AbsenceController::class, 'destroy'])->name('destroy');
        Route::post('/{absence}/approve', [AbsenceController::class, 'approve'])->name('approve');
        Route::post('/{absence}/reject', [AbsenceController::class, 'reject'])->name('reject');
    });
    
    // Salary
    Route::prefix('salary')->name('salary.')->group(function () {
        Route::get('/', [SalaryController::class, 'index'])->name('index');
        Route::get('/export', [SalaryController::class, 'export'])->name('export');
        Route::get('/setting', [SalaryController::class, 'setting'])->name('setting');
        Route::post('/generate-all', [SalaryController::class, 'generateAll'])->name('generate-all');
        Route::get('/{employee}/create', [SalaryController::class, 'create'])->name('create');
        Route::get('/{employee}', [SalaryController::class, 'show'])->name('show');
        Route::post('/{employee}', [SalaryController::class, 'store'])->name('update');
        Route::patch('/{salary}/validate', [SalaryController::class, 'validateSalary'])->name('validate');
        Route::patch('/{salary}/paid', [SalaryController::class, 'markPaid'])->name('paid');
        Route::delete('/{salary}', [SalaryController::class, 'destroy'])->name('destroy');
        Route::get('/{salary}/pdf', [SalaryController::class, 'pdf'])->name('pdf');
    });
    
    // Variables
    Route::prefix('variables')->name('variables.')->group(function () {
        Route::get('/', [VariableElementController::class, 'index'])->name('index');
        Route::post('/', [VariableElementController::class, 'store'])->name('store');
        Route::delete('/{variableElement}', [VariableElementController::class, 'destroy'])->name('destroy');
    });
    
    // Payroll settings
    Route::get('/salary/settings', [PayrollSettingController::class, 'index'])->name('payroll.settings');
    Route::put('/salary/settings', [PayrollSettingController::class, 'update'])->name('payroll.settings.update');
    
    // Notifications
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    
    // API
    Route::prefix('api')->group(function () {
        Route::get('/dashboard/stats', [DashboardController::class, 'stats']);
        Route::get('/planning/events', [PlanningController::class, 'events']);
        Route::get('/notifications/data', [NotificationController::class, 'data'])->name('api.notifications.data');
    });
});
