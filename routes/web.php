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
use App\Http\Controllers\EmployeeDashboardController;
use App\Http\Controllers\AssistantRhController;
use App\Http\Controllers\PointageController;
use App\Http\Controllers\Badge\BadgeAuthController;
use App\Http\Controllers\Badge\BadgeDashboardController;
use App\Http\Controllers\Badge\BadgePointageController;
use App\Models\User;
use App\Models\Employee;

// ── Routes publiques ──────────────────────────────────────────────────────
Route::get('/actualites', [NewsController::class, 'index']);
Route::get('/holidays/debug', [App\Http\Controllers\HolidayController::class, 'debug']);
Route::get('/holidays/{year}/{month}', [App\Http\Controllers\HolidayController::class, 'index']);
Route::get('/temps/vue-ensemble', [VueEnsembleController::class, 'index'])->name('temps.vue-ensemble');

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

// ── Auth ──────────────────────────────────────────────────────────────────
Route::get('/login',  [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout',[AuthController::class, 'logout'])->name('logout');

// ── Routes protégées (auth) ───────────────────────────────────────────────
Route::middleware(['auth'])->group(function () {

    Route::get('/', function () {
        $user = auth()->user();
        if ($user && $user->role === User::ROLE_EMPLOYEE) {
            return redirect()->route('employee.dashboard');
        }
        return redirect()->route('dashboard');
    });

    Route::get('/dashboard',          [DashboardController::class,     'index'])->name('dashboard');
    Route::get('/employee/dashboard', [EmployeeDashboardController::class, 'index'])->name('employee.dashboard');
    Route::get('/profile',            [ProfileController::class,       'index'])->name('profile');

    // ── Employees — routes spécifiques AVANT le resource ─────────────────
    Route::get('/employees/export',                        [EmployeeController::class, 'export'])         ->name('employees.export');
    Route::get('/employees/export-pdf',                    [EmployeeController::class, 'exportPdf'])      ->name('employees.export-pdf');
    Route::get('/employees/export-pdf-dept/{department}',  [EmployeeController::class, 'exportPdfByDept'])->name('employees.export-pdf-dept');
    Route::get('/employees/ajax',                          [EmployeeController::class, 'ajaxIndex'])      ->name('employees.ajax');
    Route::post('/employees/reorder',                      [EmployeeController::class, 'reorder'])        ->name('employees.reorder');
    Route::post('/employees/{employee}/regenerate-pin',    [EmployeeController::class, 'regeneratePin'])  ->name('employees.regenerate-pin');
    Route::resource('employees', EmployeeController::class); // ← APRÈS les routes spécifiques

    // ── News ──────────────────────────────────────────────────────────────
    Route::resource('news', NewsController::class);

    // ── Trombinoscope ─────────────────────────────────────────────────────
    Route::get('/trombinoscope',        [TrombinoscopeController::class, 'index'])->name('trombinoscope');
    Route::get('/trombinoscope/export', [TrombinoscopeController::class, 'export'])->name('trombinoscope.export');

    // ── Planning ──────────────────────────────────────────────────────────
    Route::get('/planning/weekly',              [PlanningController::class, 'weekly'])         ->name('planning.weekly');
    Route::get('/planning/weekly/export',       [PlanningController::class, 'exportWeekly'])   ->name('planning.weekly.export');
    Route::get('/planning/weekly/pdf',          [PlanningController::class, 'exportWeeklyPdf'])->name('planning.weekly.pdf');
    Route::get('/planning/global',              [PlanningController::class, 'global'])         ->name('planning.global');
    Route::get('/planning/monthly',             [PlanningController::class, 'monthly'])        ->name('planning.monthly');
    Route::get('/planning/monthly/export',      [PlanningController::class, 'exportMonthly'])  ->name('planning.monthly.export');
    Route::get('/planning/monthly/pdf',         [PlanningController::class, 'exportMonthlyPdf'])->name('planning.monthly.pdf');
    Route::get('/planning/show/{employee?}',    fn() => redirect()->route('planning.weekly'))  ->name('planning.show');
    Route::post('/planning',                    [PlanningController::class, 'store'])           ->name('planning.store');
    Route::put('/planning/{planning}',          [PlanningController::class, 'update'])          ->name('planning.update');
    Route::delete('/planning/{planning}',       [PlanningController::class, 'destroy'])         ->name('planning.destroy');
    Route::post('/planning/drag-drop',          [PlanningController::class, 'updateDragDrop'])  ->name('planning.dragDrop');

    // ── Week templates ────────────────────────────────────────────────────
    Route::get('/planning/templates',           [WeekTemplateController::class, 'index'])     ->name('planning.templates.index');
    Route::get('/planning/templates/create',    [WeekTemplateController::class, 'create'])    ->name('planning.templates.create');
    Route::post('/planning/templates',          [WeekTemplateController::class, 'store'])     ->name('planning.templates.store');
    Route::delete('/planning/templates/{template}', [WeekTemplateController::class, 'destroy'])->name('planning.templates.destroy');
    Route::get('/planning/templates/apply',     [WeekTemplateController::class, 'applyForm'])->name('planning.templates.apply.form');
    Route::post('/planning/templates/apply',    [WeekTemplateController::class, 'apply'])    ->name('planning.templates.apply');

    // ── Absences ──────────────────────────────────────────────────────────
    Route::prefix('absences')->name('absences.')->group(function () {
        Route::get('/',               [AbsenceController::class, 'index'])       ->name('index');
        Route::get('/create',         [AbsenceController::class, 'create'])      ->name('create');
        Route::post('/',              [AbsenceController::class, 'store'])
            ->middleware('ensure.absence.employee')                               ->name('store');
        Route::get('/calendar',       [AbsenceController::class, 'calendar'])    ->name('calendar');
        Route::get('/counters',       [AbsenceController::class, 'counters'])    ->name('counters');
        Route::get('/counters/export',[AbsenceController::class, 'countersExport'])->name('counters.export');
        Route::get('/conflicts',      [AbsenceController::class, 'getConflicts'])->name('conflicts.json');
        Route::get('/droits/export',  [AbsenceController::class, 'droitsExport'])->name('droits.export');
        Route::get('/export',         [AbsenceController::class, 'export'])      ->name('export');
        Route::get('/{absence}',      [AbsenceController::class, 'show'])        ->name('show');
        Route::get('/{absence}/edit', [AbsenceController::class, 'edit'])        ->name('edit');
        Route::put('/{absence}',      [AbsenceController::class, 'update'])      ->name('update');
        Route::delete('/{absence}',   [AbsenceController::class, 'destroy'])     ->name('destroy');
        Route::post('/{absence}/approve', [AbsenceController::class, 'approve'])->name('approve');
        Route::post('/{absence}/reject',  [AbsenceController::class, 'reject'])  ->name('reject');
    });

    // ── Salary ────────────────────────────────────────────────────────────
    Route::prefix('salary')->name('salary.')->group(function () {
        Route::get('/',                [SalaryController::class, 'index'])         ->middleware('role:admin,rh')->name('index');
        Route::get('/export',          [SalaryController::class, 'export'])        ->middleware('role:admin,rh')->name('export');
        Route::get('/setting',         [SalaryController::class, 'setting'])       ->middleware('role:admin,rh')->name('setting');
        Route::post('/generate-all',   [SalaryController::class, 'generateAll'])   ->middleware('role:admin,rh')->name('generate-all');
        Route::get('/{employee}/create',[SalaryController::class, 'create'])       ->middleware('role:admin,rh')->name('create');
        Route::post('/{employee}',     [SalaryController::class, 'store'])         ->middleware('role:admin,rh')->name('update');
        Route::patch('/{salary}/validate',[SalaryController::class, 'validateSalary'])->middleware('role:admin,rh')->name('validate');
        Route::patch('/{salary}/paid', [SalaryController::class, 'markPaid'])      ->middleware('role:admin,rh')->name('paid');
        Route::delete('/{salary}',     [SalaryController::class, 'destroy'])       ->middleware('role:admin,rh')->name('destroy');
        Route::get('/{salary}/pdf',    [SalaryController::class, 'pdf'])           ->name('pdf');
        Route::get('/{employee}',      [SalaryController::class, 'show'])          ->name('show');
    });

    // ── Payroll settings ──────────────────────────────────────────────────
    Route::get('/salary/settings', [PayrollSettingController::class, 'index'])->name('payroll.settings');
    Route::put('/salary/settings', [PayrollSettingController::class, 'update'])->name('payroll.settings.update');

    // ── Variables ─────────────────────────────────────────────────────────
    Route::prefix('variables')->name('variables.')->group(function () {
        Route::get('/',                [VariableElementController::class, 'index'])  ->name('index');
        Route::post('/',               [VariableElementController::class, 'store'])  ->name('store');
        Route::delete('/{variableElement}', [VariableElementController::class, 'destroy'])->name('destroy');
    });

    // ── Notifications ─────────────────────────────────────────────────────
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');

    // ── API ───────────────────────────────────────────────────────────────
    Route::prefix('api')->group(function () {
        Route::get('/dashboard/stats',    [DashboardController::class,    'stats']);
        Route::get('/dashboard/data',     [DashboardController::class,    'data']);
        Route::get('/planning/events',    [PlanningController::class,     'events']);
        Route::get('/notifications/data', [NotificationController::class, 'data'])->name('api.notifications.data');
    });

    // ── Assistant RH ──────────────────────────────────────────────────────
    Route::post('/assistant-rh/chat', AssistantRhController::class)->name('assistant-rh.chat');

    // ── Pointage admin ────────────────────────────────────────────────────
    Route::middleware(['role:admin,rh'])->group(function () {
        Route::get('/pointage',                            [PointageController::class, 'index'])         ->name('pointage.index');
        Route::post('/pointage/valider-journee',           [PointageController::class, 'validerJournee'])->name('pointage.valider-journee');
        Route::post('/pointage/{pointage}/toggle-valider', [PointageController::class, 'toggleValider']) ->name('pointage.toggle-valider');
        Route::post('/pointage/{pointage}/toggle-ignore',  [PointageController::class, 'toggleIgnore'])  ->name('pointage.toggle-ignore');
        Route::put('/pointage/{pointage}',                 [PointageController::class, 'update'])         ->name('pointage.update');
        Route::post('/pointage/toggle-absence',            [PointageController::class, 'toggleAbsence']) ->name('pointage.toggle-absence');
    });
});
Route::get('/pointage/pdf', [PointageController::class, 'exportPdf'])->name('pointage.pdf');
// ── Divers ────────────────────────────────────────────────────────────────
Route::post('/ask-ai', [AssistantRhController::class, '__invoke']);

Route::get('/pdf/{filename}',      [App\Http\Controllers\PdfDownloadController::class, 'download'])->name('pdf.download');
Route::get('/pdf/{filename}/view', [App\Http\Controllers\PdfDownloadController::class, 'stream'])  ->name('pdf.view');

// ── Badge ─────────────────────────────────────────────────────────────────
Route::prefix('badge')->name('badge.')->group(function () {

    Route::get('/', function () {
        return view('badge.pointage');
    })->name('pointage');

    Route::get('/auth/{action?}', [BadgeAuthController::class, 'showAuth'])->name('auth.show');
    Route::post('/auth/validate', [BadgeAuthController::class, 'authAction'])->name('auth.validate');

    Route::middleware(['badge.auth'])->group(function () {
        Route::post('/logout',   [BadgeAuthController::class,    'logout'])      ->name('logout');
        Route::get('/dashboard', [BadgeDashboardController::class,'index'])      ->name('dashboard');
        Route::post('/entree',   [BadgePointageController::class, 'entree'])     ->name('entree');
        Route::post('/sortie',   [BadgePointageController::class, 'sortie'])     ->name('sortie');
        Route::post('/action',   [BadgePointageController::class, 'handleAction'])->name('action');
        Route::get('/result',    [BadgePointageController::class, 'result'])     ->name('result');
    });
});