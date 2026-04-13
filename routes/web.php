<?php

use App\Http\Controllers\AbsenceController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\EmployeeDashboardController;
use App\Http\Controllers\NewsController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PlanningController;
use App\Http\Controllers\PointageController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SalaryController;
use App\Http\Controllers\TrombinoscopeController;
use App\Http\Controllers\VueEnsembleController;
use App\Http\Controllers\WeekTemplateController;
use App\Http\Controllers\SuperAdmin\DashboardController as SuperAdminDashboard;
use App\Http\Controllers\SuperAdmin\TenantController;
use App\Http\Controllers\SuperAdmin\SettingsController as SuperAdminSettings;
use App\Http\Controllers\SuperAdmin\ClientController;
use App\Http\Controllers\SuperAdmin\RoleController;
use Illuminate\Support\Facades\Route;

// ─── Utilitaires dev ─────────────────────────────────────────────────────────
Route::get('/link-users', function () {
    $linked = 0;
    foreach (\App\Models\User::all() as $user) {
        $employee = \App\Models\Employee::where('email', $user->email)->first();
        if ($employee && ! $employee->user_id) {
            $employee->user_id = $user->id;
            $employee->save();
            $linked++;
            echo "Linked: {$user->email} → {$employee->first_name} {$employee->last_name}<br>";
        }
    }
    echo $linked > 0 ? "<br>Linked {$linked} user(s)!" : "No new links.";
});

Route::get('/holidays/debug',         [\App\Http\Controllers\HolidayController::class, 'debug']);
Route::get('/holidays/{year}/{month}', [\App\Http\Controllers\HolidayController::class, 'index']);
Route::get('/actualites',             [NewsController::class, 'index']);

// ─── Auth (public) ────────────────────────────────────────────────────────────
Route::get('/login',   [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login',  [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// ─── SuperAdmin ───────────────────────────────────────────────────────────────
Route::middleware(['auth', 'superadmin'])
    ->prefix('superadmin')
    ->name('superadmin.')
    ->group(function () {
        Route::get('/dashboard', [SuperAdminDashboard::class, 'index'])->name('dashboard');

        Route::get('/personnalise',  [SuperAdminSettings::class, 'index'])->name('personnalise.index');
        Route::post('/personnalise', [SuperAdminSettings::class, 'update'])->name('personnalise.update');

        // ── Settings (Accès Clients uniquement) ───────────────────────────────
        Route::get('/settings', [SuperAdminSettings::class, 'index'])->name('settings.index');
        Route::post('/settings/clients/{user}/access', [SuperAdminSettings::class, 'updateClientAccess'])
             ->name('settings.clients.updateAccess');

        // ── Tenants ───────────────────────────────────────────────────────────
        Route::resource('tenants', TenantController::class);
        Route::post('tenants/{tenant}/suspend',    [TenantController::class, 'suspend'])->name('tenants.suspend');
        Route::post('tenants/{tenant}/reactivate', [TenantController::class, 'reactivate'])->name('tenants.reactivate');

        // ── Clients ───────────────────────────────────────────────────────────
        Route::get('clients',          [ClientController::class, 'index'])->name('clients.index');
        Route::get('clients/{tenant}', [ClientController::class, 'show'])->name('clients.show');

        // ── Rôles ─────────────────────────────────────────────────────────────
        Route::get('roles', [RoleController::class, 'index'])->name('roles.index');
    });

// ─── Application principale (tenant requis) ───────────────────────────────────
Route::middleware(['web', 'domain-tenant', 'auth', 'identify-tenant'])->group(function () {

    Route::middleware(['tenant-user'])->group(function () {
        Route::get('/profile',                [ProfileController::class, 'index'])->name('profile');
        Route::get('/notifications',          [NotificationController::class, 'index'])->name('notifications.index');
        Route::get('/api/notifications/data', [NotificationController::class, 'data'])->name('api.notifications.data');
        Route::get('/trombinoscope', [TrombinoscopeController::class, 'index'])->name('trombinoscope');
        Route::get('/employees/{employee}', [EmployeeController::class, 'show'])->name('employees.show');

        Route::prefix('planning')->name('planning.')->group(function () {
            Route::get('/weekly',           [PlanningController::class, 'weekly'])->name('weekly');
            Route::get('/monthly',          [PlanningController::class, 'monthly'])->name('monthly');
            Route::get('/show/{employee?}', fn () => redirect()->route('planning.weekly'))->name('show');
        });

        Route::prefix('absences')->name('absences.')->group(function () {
            Route::get('/',               [AbsenceController::class, 'index'])->name('index');
            Route::get('/create',         [AbsenceController::class, 'create'])->name('create');
            Route::post('/',              [AbsenceController::class, 'store'])->name('store');
            Route::get('/calendar',       [AbsenceController::class, 'calendar'])->name('calendar');
            Route::get('/counters',       [AbsenceController::class, 'counters'])->name('counters');
            Route::get('/{absence}',      [AbsenceController::class, 'show'])->name('show');
            Route::get('/{absence}/edit', [AbsenceController::class, 'edit'])->name('edit');
            Route::put('/{absence}',      [AbsenceController::class, 'update'])->name('show');
            Route::delete('/{absence}',   [AbsenceController::class, 'destroy'])->name('destroy');
        });
    });

    Route::middleware(['employee'])->group(function () {
        Route::get('/employer/dashboard', [EmployeeDashboardController::class, 'index'])->name('employee.dashboard');
    });

    Route::middleware(['admin'])->group(function () {
        Route::get('/', fn () => redirect()->route('admin.dashboard'));
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('admin.dashboard');

        Route::resource('news',      NewsController::class);
        Route::resource('employees', EmployeeController::class)->except(['show']);

        Route::get('/temps/vue-ensemble', [VueEnsembleController::class, 'index'])->name('temps.vue-ensemble');

        Route::prefix('planning')->name('planning.')->group(function () {
            Route::get('/global',        [PlanningController::class, 'global'])->name('global');
            Route::post('/',             [PlanningController::class, 'store'])->name('store');
            Route::put('/{planning}',    [PlanningController::class, 'update'])->name('update');
            Route::delete('/{planning}', [PlanningController::class, 'destroy'])->name('destroy');
            Route::post('/drag-drop',    [PlanningController::class, 'updateDragDrop'])->name('dragDrop');

            Route::prefix('templates')->name('templates.')->group(function () {
                Route::get('/',              [WeekTemplateController::class, 'index'])->name('index');
                Route::get('/create',        [WeekTemplateController::class, 'create'])->name('create');
                Route::post('/',             [WeekTemplateController::class, 'store'])->name('store');
                Route::delete('/{template}', [WeekTemplateController::class, 'destroy'])->name('destroy');
                Route::get('/apply',         [WeekTemplateController::class, 'applyForm'])->name('apply');
                Route::post('/apply',        [WeekTemplateController::class, 'apply'])->name('apply.post');
            });
        });

        Route::get('/pointage',                            [PointageController::class, 'index'])->name('pointage.index');
        Route::post('/pointage/valider-journee',           [PointageController::class, 'validerJournee'])->name('pointage.valider-journee');
        Route::post('/pointage/{pointage}/toggle-valider', [PointageController::class, 'toggleValider'])->name('pointage.toggle-valider');
        Route::post('/pointage/{pointage}/toggle-ignore',  [PointageController::class, 'toggleIgnore'])->name('pointage.toggle-ignore');
        Route::put('/pointage/{pointage}',                 [PointageController::class, 'update'])->name('pointage.update');

        Route::prefix('absences')->name('absences.')->group(function () {
            Route::post('/{absence}/approve', [AbsenceController::class, 'approve'])->name('approve');
            Route::post('/{absence}/reject',  [AbsenceController::class, 'reject'])->name('reject');
        });

        Route::prefix('salary')->name('salary.')->group(function () {
            Route::get('/',                    [SalaryController::class, 'index'])->name('index');
            Route::post('/generate-all',       [SalaryController::class, 'generateAll'])->name('generate-all');
            Route::get('/{employee}/create',   [SalaryController::class, 'create'])->name('create');
            Route::get('/{employee}',          [SalaryController::class, 'show'])->name('show');
            Route::post('/{employee}',         [SalaryController::class, 'store'])->name('update');
            Route::patch('/{salary}/validate', [SalaryController::class, 'validateSalary'])->name('validate');
            Route::patch('/{salary}/paid',     [SalaryController::class, 'markPaid'])->name('paid');
            Route::delete('/{salary}',         [SalaryController::class, 'destroy'])->name('destroy');
            Route::get('/{salary}/pdf',        [SalaryController::class, 'pdf'])->name('pdf');
        });

        Route::prefix('api')->group(function () {
            Route::get('/dashboard/stats', [DashboardController::class, 'stats']);
            Route::get('/planning/events', [PlanningController::class, 'events']);
        });
    });
});
