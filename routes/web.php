<?php

use App\Http\Controllers\AbsenceController;
use App\Http\Controllers\AssistantRhController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Badge\BadgeAuthController;
use App\Http\Controllers\Badge\BadgeDashboardController;
use App\Http\Controllers\Badge\BadgePointageController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\EmployeeDashboardController;
use App\Http\Controllers\NewsController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PdfDownloadController;
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

// ─── DEBUG temporaire — À SUPPRIMER APRÈS TEST ────────────────────────────────
Route::get('/debug-ai-key', function () {
    $configKey = config('ai.providers.openrouter.key');
    $envKey    = env('OPENROUTER_API_KEY');

    return response()->json([
        'config_key'    => $configKey ? '✅ Trouvée' : '❌ Null',
        'env_key'       => $envKey    ? '✅ Trouvée' : '❌ Null',
        'key_preview'   => $configKey ? substr($configKey, 0, 12) . '...' : 'N/A',
        'app_env'       => config('app.env'),
        'config_cached' => app()->configurationIsCached() ? '✅ Oui (config:cache actif)' : '❌ Non',
    ]);
})->middleware('auth');
// ─────────────────────────────────────────────────────────────────────────────

Route::get('/holidays/debug',         [\App\Http\Controllers\HolidayController::class, 'debug']);
Route::get('/holidays/{year}/{month}', [\App\Http\Controllers\HolidayController::class, 'index']);
Route::get('/actualites',             [NewsController::class, 'index']);

// ─── PDF Download ─────────────────────────────────────────────────────────────
Route::get('/pdf/{filename}', [PdfDownloadController::class, 'download'])
    ->where('filename', '[^/]+\.pdf')
    ->name('pdf.download');
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

        Route::get('/settings', [SuperAdminSettings::class, 'index'])->name('settings.index');
        Route::post('/settings/clients/{user}/access', [SuperAdminSettings::class, 'updateClientAccess'])
             ->name('settings.clients.updateAccess');

        Route::resource('tenants', TenantController::class);
        Route::post('tenants/{tenant}/suspend',    [TenantController::class, 'suspend'])->name('tenants.suspend');
        Route::post('tenants/{tenant}/reactivate', [TenantController::class, 'reactivate'])->name('tenants.reactivate');

        Route::get('clients',          [ClientController::class, 'index'])->name('clients.index');
        Route::get('clients/{tenant}', [ClientController::class, 'show'])->name('clients.show');

        Route::get('roles', [RoleController::class, 'index'])->name('roles.index');
    });

// ─── Application principale (tenant requis) ───────────────────────────────────
Route::middleware(['web', 'domain-tenant', 'auth', 'identify-tenant'])->group(function () {

    // ── Accessible à tous les utilisateurs connectés du tenant ───────────────
    Route::middleware(['tenant-user'])->group(function () {

        // CORRECTION : ->name('assistant-rh.chat') ajouté pour correspondre
        // à l'appel route('assistant-rh.chat') dans layouts/app.blade.php
        Route::post('/ask-ai', AssistantRhController::class)->name('assistant-rh.chat');

        Route::get('/profile',                [ProfileController::class, 'index'])->name('profile');
        Route::get('/notifications',          [NotificationController::class, 'index'])->name('notifications.index');
        Route::get('/api/notifications/data', [NotificationController::class, 'data'])->name('api.notifications.data');
        Route::get('/trombinoscope',          [TrombinoscopeController::class, 'index'])->name('trombinoscope');

        // !! show ici, AVANT les routes admin qui enregistrent {employee} !!
        Route::get('/employees/{employee}', [EmployeeController::class, 'show'])->where('employee', '[0-9]+')->name('employees.show');

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
            Route::get('/conflicts.json', [AbsenceController::class, 'getConflicts'])->name('conflicts.json');
            Route::get('/{absence}',      [AbsenceController::class, 'show'])->name('show');
            Route::get('/{absence}/edit', [AbsenceController::class, 'edit'])->name('edit');
            Route::put('/{absence}',      [AbsenceController::class, 'update'])->name('update');
            Route::delete('/{absence}',   [AbsenceController::class, 'destroy'])->name('destroy');
        });

        Route::prefix('salary')->name('salary.')->group(function () {
            Route::get('/{employee}',   [SalaryController::class, 'show'])->name('show');
            Route::get('/{salary}/pdf', [SalaryController::class, 'pdf'])->name('pdf');
        });
    });

    // ── Employé connecté (dashboard employé) ─────────────────────────────────
    Route::middleware(['employee'])->group(function () {
        Route::get('/employer/dashboard', [EmployeeDashboardController::class, 'index'])->name('employee.dashboard');
    });

    // ── Admin seulement ───────────────────────────────────────────────────────
    Route::middleware(['admin'])->group(function () {
        Route::get('/', fn () => redirect()->route('admin.dashboard'));
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('admin.dashboard');

        Route::resource('news', NewsController::class);

        // ── Routes employees : segments statiques EN PREMIER ─────────────────
        Route::get('employees',                              [EmployeeController::class, 'index'])->name('employees.index');
        Route::get('employees/create',                       [EmployeeController::class, 'create'])->name('employees.create');
        Route::post('employees',                             [EmployeeController::class, 'store'])->name('employees.store');
        Route::get('employees/export-pdf',                   [EmployeeController::class, 'exportPdf'])->name('employees.export-pdf');
        Route::get('employees/export-pdf-dept/{department}', [EmployeeController::class, 'exportPdfByDept'])->name('employees.export-pdf-dept');
        Route::post('employees/reorder',                     [EmployeeController::class, 'reorder'])->name('employees.reorder');
        Route::get('employees/ajax',                         [EmployeeController::class, 'ajax'])->name('employees.ajax');

        // ── Routes employees : segments dynamiques {employee} ENSUITE ─────────
        Route::post('employees/{employee}/regenerate-pin', [EmployeeController::class, 'regeneratePin'])->name('employees.regeneratePin');
        Route::resource('employees', EmployeeController::class)->only(['edit', 'update', 'destroy']);
        // ─────────────────────────────────────────────────────────────────────

        Route::get('/temps/vue-ensemble', [VueEnsembleController::class, 'index'])->name('temps.vue-ensemble');

        Route::prefix('planning')->name('planning.')->group(function () {
            Route::get('/global',        [PlanningController::class, 'global'])->name('global');
            Route::post('/',             [PlanningController::class, 'store'])->name('store');
            Route::put('/{planning}',    [PlanningController::class, 'update'])->name('update');
            Route::delete('/{planning}', [PlanningController::class, 'destroy'])->name('destroy');
            Route::post('/drag-drop',    [PlanningController::class, 'updateDragDrop'])->name('dragDrop');
            Route::get('weekly/pdf',     [PlanningController::class, 'exportWeeklyPdf'])->name('exportWeeklyPdf');
            Route::get('monthly/pdf',    [PlanningController::class, 'exportMonthlyPdf'])->name('monthly.pdf');

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
        Route::get('/pointage/pdf',                        [PointageController::class, 'exportPdf'])->name('pointage.pdf');
        Route::post('/pointage/toggle-absence',            [PointageController::class, 'toggleAbsence'])->name('pointage.toggle-absence');
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
            Route::post('/{employee}',         [SalaryController::class, 'store'])->name('update');
            Route::patch('/{salary}/validate', [SalaryController::class, 'validateSalary'])->name('validate');
            Route::patch('/{salary}/paid',     [SalaryController::class, 'markPaid'])->name('paid');
            Route::delete('/{salary}',         [SalaryController::class, 'destroy'])->name('destroy');
        });

        Route::middleware(['admin'])->prefix('variables')->name('variables.')->group(function () {
            Route::get('/', [\App\Http\Controllers\VariableElementController::class, 'index'])->name('index');
        });

        Route::prefix('api')->group(function () {
            Route::get('/dashboard/stats', [DashboardController::class, 'stats']);
            Route::get('/planning/events', [PlanningController::class, 'events']);
        });

        //── Badge ─────────────────────────────────────────────────────────────────
        Route::prefix('badge')->name('badge.')->group(function () {

            Route::get('/', function () {
                return view('badge.pointage');
            })->name('pointage');

            Route::get('/auth/{action?}', [BadgeAuthController::class, 'showAuth'])->name('auth.show');
            Route::post('/auth/validate', [BadgeAuthController::class, 'authAction'])->name('auth.validate');

            Route::middleware(['badge.auth'])->group(function () {
                Route::post('/logout',   [BadgeAuthController::class,     'logout'])       ->name('logout');
                Route::get('/dashboard', [BadgeDashboardController::class, 'index'])       ->name('dashboard');
                Route::post('/entree',   [BadgePointageController::class,  'entree'])      ->name('entree');
                Route::post('/sortie',   [BadgePointageController::class,  'sortie'])      ->name('sortie');
                Route::post('/action',   [BadgePointageController::class,  'handleAction'])->name('action');
                Route::get('/result',    [BadgePointageController::class,  'result'])      ->name('result');
            });
        });
    });
});
