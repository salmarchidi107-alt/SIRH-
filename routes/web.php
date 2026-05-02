<?php

use App\Http\Controllers\AbsenceController;
use App\Http\Controllers\AssistantRhController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Badge\BadgeAuthController;
use App\Http\Controllers\Badge\BadgeDashboardController;
use App\Http\Controllers\Badge\BadgePointageController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\DocumentModeleController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\EmployeeDashboardController;
use App\Http\Controllers\NewsController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PayrollSettingController;
use App\Http\Controllers\PdfDownloadController;
use App\Http\Controllers\PlanningController;
use App\Http\Controllers\PointageController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RoomController;
use App\Http\Controllers\SalaryController;
use App\Http\Controllers\TrombinoscopeController;
use App\Http\Controllers\VariableElementController;
use App\Http\Controllers\VueEnsembleController;
use App\Http\Controllers\WeekTemplateController;
use App\Http\Controllers\SuperAdmin\DashboardController as SuperAdminDashboard;
use App\Http\Controllers\SuperAdmin\TenantController;
use App\Http\Controllers\SuperAdmin\SettingsController as SuperAdminSettings;
use App\Http\Controllers\SuperAdmin\ClientController;
use App\Http\Controllers\SuperAdmin\RoleController;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DocumentEnteteController;
use App\Http\Controllers\ParametrageController;
use App\Http\Controllers\DepartmentController;

// ═════════════════════════════════════════════════════════════════════════════
// DEBUG TEMPORAIRE — À SUPPRIMER APRÈS TEST
// ═════════════════════════════════════════════════════════════════════════════
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

// ═════════════════════════════════════════════════════════════════════════════
// ROUTES PUBLIQUES
// ═════════════════════════════════════════════════════════════════════════════
Route::get('/holidays/debug',          [\App\Http\Controllers\HolidayController::class, 'debug']);
Route::get('/holidays/{year}/{month}', [\App\Http\Controllers\HolidayController::class, 'index']);
Route::get('/actualites',              [NewsController::class, 'index']);

Route::get('/pdf/{filename}',          [PdfDownloadController::class, 'download'])->where('filename', '[^/]+\.pdf')->name('pdf.download');
Route::get('/pdf/{filename}/view',     [PdfDownloadController::class, 'stream'])->where('filename', '[^/]+\.pdf')->name('pdf.view');

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

// ═════════════════════════════════════════════════════════════════════════════
// AUTH
// ═════════════════════════════════════════════════════════════════════════════
Route::get('/login',   [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login',  [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// ═════════════════════════════════════════════════════════════════════════════
// SUPERADMIN
// ═════════════════════════════════════════════════════════════════════════════
Route::middleware(['auth', 'superadmin'])
    ->prefix('superadmin')
    ->name('superadmin.')
    ->group(function () {

        Route::get('/dashboard', [SuperAdminDashboard::class, 'index'])->name('dashboard');

        Route::get('/personnalise',  [SuperAdminSettings::class, 'index'])->name('personnalise.index');
        Route::post('/personnalise', [SuperAdminSettings::class, 'update'])->name('personnalise.update');

        Route::get('/settings',  [SuperAdminSettings::class, 'index'])->name('settings.index');
        Route::post('/settings/clients/{user}/access', [SuperAdminSettings::class, 'updateClientAccess'])
             ->name('settings.clients.updateAccess');

        Route::resource('tenants', TenantController::class);
        Route::post('tenants/{tenant}/suspend',    [TenantController::class, 'suspend'])->name('tenants.suspend');
        Route::post('tenants/{tenant}/reactivate', [TenantController::class, 'reactivate'])->name('tenants.reactivate');

        Route::get('clients',          [ClientController::class, 'index'])->name('clients.index');
        Route::get('clients/{tenant}', [ClientController::class, 'show'])->name('clients.show');

        Route::get('roles', [RoleController::class, 'index'])->name('roles.index');
    });

// ═════════════════════════════════════════════════════════════════════════════
// APPLICATION PRINCIPALE — tenant + auth
// (fusion du groupe domain-tenant de routes1 + middleware auth de routes2)
// ═════════════════════════════════════════════════════════════════════════════
Route::middleware(['web', 'domain-tenant', 'auth', 'identify-tenant'])->group(function () {

    // ── Redirection racine ────────────────────────────────────────────────
    Route::get('/', function () {
        $user = auth()->user();
        if ($user && $user->role === User::ROLE_EMPLOYEE) {
            return redirect()->route('employee.dashboard');
        }
        return redirect()->route('admin.dashboard');
    });

    // ─────────────────────────────────────────────────────────────────────
    // ACCESSIBLE À TOUS LES UTILISATEURS CONNECTÉS DU TENANT
    // ─────────────────────────────────────────────────────────────────────
    Route::middleware(['tenant-user'])->group(function () {

        Route::post('/ask-ai',           AssistantRhController::class)->name('assistant-rh.chat');
        Route::post('/assistant-rh/chat', AssistantRhController::class);

        Route::get('/profile',               [ProfileController::class,       'index'])->name('profile');
        Route::get('/notifications',         [NotificationController::class,  'index'])->name('notifications.index');
        Route::get('/api/notifications/data',[NotificationController::class,  'data']) ->name('api.notifications.data');
        Route::get('/trombinoscope',         [TrombinoscopeController::class, 'index'])->name('trombinoscope');
        Route::get('/trombinoscope/export',  [TrombinoscopeController::class, 'export'])->name('trombinoscope.export');

        // Employee show — AVANT les routes admin {employee}
        Route::get('/employees/{employee}', [EmployeeController::class, 'show'])
            ->where('employee', '[0-9]+')
            ->name('employees.show');

        // Planning (lecture) accessible à tous
        Route::prefix('planning')->name('planning.')->group(function () {
            Route::get('/weekly',           [PlanningController::class, 'weekly'])->name('weekly');
            Route::get('/monthly',          [PlanningController::class, 'monthly'])->name('monthly');
            Route::get('/show/{employee?}', fn () => redirect()->route('planning.weekly'))->name('show');
        });

        // Absences (lecture + demandes)
        Route::prefix('absences')->name('absences.')->group(function () {
            Route::get('/',                [AbsenceController::class, 'index'])          ->name('index');
            Route::get('/create',          [AbsenceController::class, 'create'])         ->name('create');
            Route::post('/',               [AbsenceController::class, 'store'])
                ->middleware('ensure.absence.employee')                                   ->name('store');
            Route::get('/calendar',        [AbsenceController::class, 'calendar'])       ->name('calendar');
            Route::get('/counters',        [AbsenceController::class, 'counters'])       ->name('counters');
            Route::get('/counters/export', [AbsenceController::class, 'countersExport'])->name('counters.export');
            Route::get('/conflicts',       [AbsenceController::class, 'getConflicts'])   ->name('conflicts.json');
            Route::get('/droits/export',   [AbsenceController::class, 'droitsExport'])   ->name('droits.export');
            Route::get('/export',          [AbsenceController::class, 'export'])         ->name('export');
            Route::get('/{absence}',       [AbsenceController::class, 'show'])           ->name('show');
            Route::get('/{absence}/edit',  [AbsenceController::class, 'edit'])           ->name('edit');
            Route::put('/{absence}',       [AbsenceController::class, 'update'])         ->name('update');
            Route::delete('/{absence}',    [AbsenceController::class, 'destroy'])        ->name('destroy');
            Route::get('/{absence}/pdf',   [AbsenceController::class, 'downloadPdf'])    ->name('pdf');
        });

        // Salary (lecture + PDF — accessible employé)
        Route::prefix('salary')->name('salary.')->group(function () {
            Route::get('/{salary}/pdf', [SalaryController::class, 'pdf'])  ->name('pdf');
            Route::get('/{employee}',   [SalaryController::class, 'show']) ->name('show');
        });
    });

    // ── Dashboard employé ─────────────────────────────────────────────────
    Route::middleware(['employee'])->group(function () {
        Route::get('/employer/dashboard', [EmployeeDashboardController::class, 'index'])->name('employee.dashboard');
    });

    // ─────────────────────────────────────────────────────────────────────
    // ADMIN UNIQUEMENT
    // ─────────────────────────────────────────────────────────────────────
    Route::middleware(['admin'])->group(function () {

        Route::get('/dashboard', [DashboardController::class, 'index'])->name('admin.dashboard');

        // ── News ──────────────────────────────────────────────────────────
        Route::resource('news', NewsController::class);

        // ── Employees — statiques AVANT dynamiques ────────────────────────
        Route::get('employees',                               [EmployeeController::class, 'index'])        ->name('employees.index');
        Route::get('employees/create',                        [EmployeeController::class, 'create'])       ->name('employees.create');
        Route::post('employees',                              [EmployeeController::class, 'store'])        ->name('employees.store');
        Route::get('employees/export',                        [EmployeeController::class, 'export'])       ->name('employees.export');
        Route::get('employees/export-pdf',                    [EmployeeController::class, 'exportPdf'])   ->name('employees.export-pdf');
        Route::get('employees/export-pdf-dept/{department}',  [EmployeeController::class, 'exportPdfByDept'])->name('employees.export-pdf-dept');
        Route::post('employees/reorder',                      [EmployeeController::class, 'reorder'])     ->name('employees.reorder');
        Route::get('employees/ajax',                          [EmployeeController::class, 'ajax'])        ->name('employees.ajax');
        Route::post('employees/{employee}/regenerate-pin',    [EmployeeController::class, 'regeneratePin'])->name('employees.regeneratePin');
        Route::resource('employees', EmployeeController::class)->only(['edit', 'update', 'destroy']);

        // ── Temps / Vue ensemble ──────────────────────────────────────────
        Route::get('/temps/vue-ensemble', [VueEnsembleController::class, 'index'])->name('temps.vue-ensemble');

        // ── Planning (écriture) ───────────────────────────────────────────
        Route::prefix('planning')->name('planning.')->group(function () {
            Route::get('/global',                   [PlanningController::class, 'global'])          ->name('global');
            Route::get('/weekly/export',            [PlanningController::class, 'exportWeekly'])   ->name('weekly.export');
Route::get('/weekly/pdf', [PlanningController::class, 'exportWeeklyPdf'])
    ->name('weekly.pdf');            Route::get('/monthly/export',           [PlanningController::class, 'exportMonthly'])  ->name('monthly.export');
            Route::get('/monthly/pdf',              [PlanningController::class, 'exportMonthlyPdf'])->name('monthly.pdf');
            Route::post('/',                        [PlanningController::class, 'store'])            ->name('store');
            Route::put('/{planning}',               [PlanningController::class, 'update'])           ->name('update');
            Route::delete('/{planning}',            [PlanningController::class, 'destroy'])          ->name('destroy');
            Route::post('/drag-drop',               [PlanningController::class, 'updateDragDrop'])   ->name('dragDrop');
            Route::post('/update-room',             [PlanningController::class, 'updateRoom'])       ->name('update.room');
            Route::get('/planning/weekly', [PlanningController::class, 'weekly'])->name('planning.weekly');
            // Templates
            Route::prefix('templates')->name('templates.')->group(function () {
                Route::get('/',               [WeekTemplateController::class, 'index'])    ->name('index');
                Route::get('/create',         [WeekTemplateController::class, 'create'])   ->name('create');
                Route::post('/',              [WeekTemplateController::class, 'store'])    ->name('store');
                Route::delete('/{template}',  [WeekTemplateController::class, 'destroy']) ->name('destroy');
                Route::get('/apply',          [WeekTemplateController::class, 'applyForm'])->name('apply');
                Route::post('/apply',         [WeekTemplateController::class, 'apply'])   ->name('apply.post');
            });
        });

        // ── Rooms ─────────────────────────────────────────────────────────
        Route::get('/rooms',  [RoomController::class, 'index'])->name('rooms.index');
        Route::post('/rooms', [RoomController::class, 'store'])->name('rooms.store');

        // ── Pointage ──────────────────────────────────────────────────────
          Route::middleware(['role:admin,rh'])->group(function () {
        Route::get('/pointage',                            [PointageController::class, 'index'])         ->name('pointage.index');
        Route::get('/pointage/pdf',                        [PointageController::class, 'exportPdf'])     ->name('pointage.pdf');
        Route::get('/pointage/badges-pin',                 [PointageController::class, 'badgesPin'])     ->name('pointage.badges-pin');
        Route::post('/pointage/regenerer-pin',             [PointageController::class, 'regenererPin'])  ->name('pointage.regenerer-pin');
        Route::post('/pointage/regenerer-tous-pins',       [PointageController::class, 'regenererTousPins'])->name('pointage.regenerer-tous-pins');
        Route::post('/pointage/valider-journee',           [PointageController::class, 'validerJournee'])->name('pointage.valider-journee');
        Route::post('/pointage/{pointage}/toggle-valider', [PointageController::class, 'toggleValider']) ->name('pointage.toggle-valider');
        Route::post('/pointage/{pointage}/toggle-ignore',  [PointageController::class, 'toggleIgnore'])  ->name('pointage.toggle-ignore');
        Route::put('/pointage/{pointage}',                 [PointageController::class, 'update'])         ->name('pointage.update');
        Route::post('/pointage/toggle-absence',            [PointageController::class, 'toggleAbsence']) ->name('pointage.toggle-absence');
    });

        // ── Absences (validation admin) ───────────────────────────────────
        Route::prefix('absences')->name('absences.')->group(function () {
            Route::post('/{absence}/approve', [AbsenceController::class, 'approve'])->name('approve');
            Route::post('/{absence}/reject',  [AbsenceController::class, 'reject']) ->name('reject');
        });

        // ── Salary (admin/RH) ─────────────────────────────────────────────
        Route::prefix('salary')->name('salary.')->middleware(['role:admin,rh'])->group(function () {
            Route::get('/',                     [SalaryController::class, 'index'])         ->name('index');
            Route::get('/export',               [SalaryController::class, 'export'])        ->name('export');
            Route::get('/setting',              [SalaryController::class, 'setting'])       ->name('setting');
            Route::post('/generate-all',        [SalaryController::class, 'generateAll'])   ->name('generate-all');
            Route::get('/{employee}/create',    [SalaryController::class, 'create'])        ->name('create');
            Route::post('/{employee}',          [SalaryController::class, 'store'])         ->name('update');
            Route::patch('/{salary}/validate',  [SalaryController::class, 'validateSalary'])->name('validate');
            Route::patch('/{salary}/paid',      [SalaryController::class, 'markPaid'])      ->name('paid');
            Route::delete('/{salary}',          [SalaryController::class, 'destroy'])       ->name('destroy');
        });

        // ── Payroll settings ──────────────────────────────────────────────
        Route::get('/salary/settings',  [PayrollSettingController::class, 'index']) ->name('payroll.settings');
        Route::put('/salary/settings',  [PayrollSettingController::class, 'update'])->name('payroll.settings.update');

        // ── Variables ─────────────────────────────────────────────────────
        Route::prefix('variables')->name('variables.')->group(function () {
            Route::get('/',                     [VariableElementController::class, 'index'])  ->name('index');
            Route::post('/',                    [VariableElementController::class, 'store'])  ->name('store');
            Route::delete('/{variableElement}', [VariableElementController::class, 'destroy'])->name('destroy');
        });

        // ── GED (documents + modèles) ─────────────────────────────────────
       Route::get('/ged',                      [DocumentController::class, 'index'])   ->name('ged.index');
Route::post('/ged',                     [DocumentController::class, 'store'])   ->name('ged.store');
Route::get('/ged/{document}/edit',      [DocumentController::class, 'edit'])    ->name('ged.edit');
Route::put('/ged/{document}',           [DocumentController::class, 'update'])  ->name('ged.update');
Route::delete('/ged/{document}',        [DocumentController::class, 'destroy']) ->name('ged.destroy');
Route::get('/ged/{document}/download',  [DocumentController::class, 'download'])->name('ged.download');


Route::get('/ged/modeles',                    [DocumentModeleController::class, 'index'])  ->name('ged.modeles.index');
Route::post('/ged/modeles',                   [DocumentModeleController::class, 'store'])  ->name('ged.modeles.store');
Route::get('/ged/modeles/{modele}/edit',      [DocumentModeleController::class, 'edit'])   ->name('ged.modeles.edit');
Route::put('/ged/modeles/{modele}',           [DocumentModeleController::class, 'update']) ->name('ged.modeles.update');
Route::delete('/ged/modeles/{modele}',        [DocumentModeleController::class, 'destroy'])->name('ged.modeles.destroy');
Route::get('/ged/entete',        [DocumentEnteteController::class, 'index'])->name('ged.entete.index');
Route::post('/ged/entete',       [DocumentEnteteController::class, 'store'])->name('ged.entete.store');

        // ── API ───────────────────────────────────────────────────────────
        Route::prefix('api')->group(function () {
            Route::get('/dashboard/stats', [DashboardController::class,   'stats']);
            Route::get('/dashboard/data',  [DashboardController::class,   'data']);
            Route::get('/planning/events', [PlanningController::class,    'events']);
        });
    });

}); // fin middleware tenant

// ═════════════════════════════════════════════════════════════════════════════
// BADGE — hors tenant (tablette autonome)
// ═════════════════════════════════════════════════════════════════════════════
Route::prefix('badge')->name('badge.')->group(function () {

    Route::get('/', function () {
        return view('badge.pointage');
    })->name('pointage');

    Route::get('/auth/{action?}', [BadgeAuthController::class, 'showAuth'])->name('auth.show');
    Route::post('/auth/Badge/validate', [BadgeAuthController::class, 'authAction'])->name('auth.validate');

    Route::middleware(['badge.auth'])->group(function () {
        Route::post('/logout',   [BadgeAuthController::class,    'logout'])      ->name('logout');
        Route::get('/dashboard', [BadgeDashboardController::class,'index'])      ->name('dashboard');
        Route::post('/entree',   [BadgePointageController::class, 'entree'])     ->name('entree');
        Route::post('/sortie',   [BadgePointageController::class, 'sortie'])     ->name('sortie');
        Route::post('/action',   [BadgePointageController::class, 'handleAction'])->name('action');
        Route::get('/result',    [BadgePointageController::class, 'result'])     ->name('result');
    });
});
Route::get('/parametrage', [ParametrageController::class, 'index'])
    ->name('parametrage.index');
 
// Salles (CRUD)
Route::post('/rooms',          [RoomController::class, 'store'])  ->name('rooms.store');
Route::put('/rooms/{room}',    [RoomController::class, 'update']) ->name('rooms.update');
Route::delete('/rooms/{room}', [RoomController::class, 'destroy'])->name('rooms.destroy');
 
// Départements (CRUD — pas de GET index car intégré dans paramétrage)
Route::post('/departments',               [DepartmentController::class, 'store'])  ->name('departments.store');
Route::put('/departments/{department}',   [DepartmentController::class, 'update']) ->name('departments.update');
Route::delete('/departments/{department}',[DepartmentController::class, 'destroy'])->name('departments.destroy');
 