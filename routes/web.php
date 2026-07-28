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
use App\Http\Controllers\ReportingController;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DocumentEnteteController;
use App\Http\Controllers\ParametrageController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\FormationController;
use App\Http\Controllers\ReferentielController;
use App\Http\Controllers\TwoFactorController;
use App\Http\Controllers\SuperAdmin\VerificationCodeController;
use App\Http\Controllers\AffectationController;
use App\Http\Controllers\DechargeController;
use App\Http\Controllers\EquipementController;
use App\Http\Controllers\RetourController;
use App\Http\Controllers\Admin\VerificationCodeController as AdminVerifCodeController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\ExpenseOCRController;
use App\Http\Controllers\Activites\Admin\DashboardController as ActivitesAdminDashboardController;
use App\Http\Controllers\Activites\Admin\ProjectController as ActivitesAdminProjectController;
use App\Http\Controllers\Activites\Admin\TaskController as ActivitesAdminTaskController;
use App\Http\Controllers\Activites\Employee\MyTaskController;
use App\Http\Controllers\Activites\Employee\TimeEntryController;


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

Route::get('/pdf/{filename}',      [PdfDownloadController::class, 'download'])->where('filename', '[^/]+\.pdf')->name('pdf.download');
Route::get('/pdf/{filename}/view', [PdfDownloadController::class, 'stream'])->where('filename',  '[^/]+\.pdf')->name('pdf.view');

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

Route::middleware(['auth'])->group(function () {
    Route::get('/verify-2fa',  [TwoFactorController::class, 'show'])  ->name('2fa.show');
    Route::post('/verify-2fa', [TwoFactorController::class, 'verify'])->name('2fa.verify');
});

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

        Route::prefix('codes')->name('codes.')->group(function () {
            Route::get('/',                              [VerificationCodeController::class, 'index'])          ->name('index');
            Route::post('/generate',                     [VerificationCodeController::class, 'generate'])       ->name('generate');
            Route::post('/assign',                       [VerificationCodeController::class, 'assign'])         ->name('assign');
            Route::post('/users/{user}/replace',         [VerificationCodeController::class, 'replace'])        ->name('replace');
            Route::get('/tenant/{tenantId}',             [VerificationCodeController::class, 'tenantCodes'])    ->name('tenant');
            Route::get('/tenant-stats/{tenantId}',       [VerificationCodeController::class, 'tenantStats'])    ->name('tenant-stats');
            Route::post('/generate-missing',             [VerificationCodeController::class, 'generateMissing'])->name('generate-missing');
            Route::post('/renew-quarter',                [VerificationCodeController::class, 'renewQuarter'])   ->name('renew-quarter');
            Route::post('/replace-user/{user}',          [VerificationCodeController::class, 'replaceForUser']) ->name('replace-user');
            Route::delete('/{verificationCode}/revoke',  [VerificationCodeController::class, 'revoke'])         ->name('revoke');
            Route::post('/force-renew',                  [VerificationCodeController::class, 'forceRenew'])     ->name('force-renew');
            Route::post('/regen-single',                 [VerificationCodeController::class, 'regenSingle'])    ->name('regen-single');
            Route::post('/regen-all',                    [VerificationCodeController::class, 'regenAll'])       ->name('regen-all');
        });
    });

// ═════════════════════════════════════════════════════════════════════════════
// APPLICATION PRINCIPALE — tenant + auth
// ═════════════════════════════════════════════════════════════════════════════
Route::middleware(['web', 'auth', 'identify-tenant', '2fa'])->group(function () {

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

        Route::post('/ask-ai',            AssistantRhController::class)->name('assistant-rh.chat');
        Route::post('/assistant-rh/chat', AssistantRhController::class);

        Route::get('/profile',                [ProfileController::class,       'index'])->name('profile');
        Route::get('/notifications',          [NotificationController::class,  'index'])->name('notifications.index');
        Route::get('/api/notifications/data', [NotificationController::class,  'data']) ->name('api.notifications.data');
        Route::get('/trombinoscope',          [TrombinoscopeController::class, 'index'])->name('trombinoscope');
        Route::get('/trombinoscope/export',   [TrombinoscopeController::class, 'export'])->name('trombinoscope.export');

        Route::get('/employees/{employee}', [EmployeeController::class, 'show'])
            ->where('employee', '[0-9]+')
            ->name('employees.show');

        // ── Planning — lecture
        Route::prefix('planning')->name('planning.')->group(function () {
            Route::get('/weekly',          [PlanningController::class, 'weekly']) ->name('weekly');
            Route::get('/monthly',         [PlanningController::class, 'monthly'])->name('monthly');
            Route::get('/show/{employee?}',fn () => redirect()->route('planning.weekly'))->name('show');
        });

        // ── Absences
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

        // ── Salary — lecture
        Route::prefix('salary')->name('salary.')->group(function () {
            Route::get('/{salary}/pdf', [SalaryController::class, 'pdf'])
                ->where('salary', '[0-9]+')
                ->name('pdf');
            Route::get('/{employee}',   [SalaryController::class, 'show'])
                ->where('employee', '[0-9]+')
                ->name('show');
        });

        // ── Vue d'ensemble temps
        Route::get('/temps/vue-ensemble', [VueEnsembleController::class, 'index'])
            ->name('temps.vue-ensemble');

        // ── Pointage — rh + admin

        Route::middleware(['role:admin,rh'])->group(function () {
            Route::get('/pointage',                            [PointageController::class, 'index'])             ->name('pointage.index');
            Route::get('/pointage/pdf',                        [PointageController::class, 'exportPdf'])         ->name('pointage.pdf');
            Route::get('/pointage/badges-pin',                 [PointageController::class, 'badgesPin'])         ->name('pointage.badges-pin');
            Route::post('/pointage/regenerer-pin',             [PointageController::class, 'regenererPin'])      ->name('pointage.regenerer-pin');
            Route::post('/pointage/regenerer-tous-pins',       [PointageController::class, 'regenererTousPins']) ->name('pointage.regenerer-tous-pins');
            Route::get('/pointage/badges-pin/export-pdf',      [PointageController::class, 'exportBadgesPinPdf'])->name('pointage.export-badges-pin-pdf');
            Route::post('/pointage/valider-journee',           [PointageController::class, 'validerJournee'])    ->name('pointage.valider-journee');
            Route::post('/pointage/{pointage}/toggle-valider', [PointageController::class, 'toggleValider'])     ->name('pointage.toggle-valider');
            Route::post('/pointage/{pointage}/toggle-ignore',  [PointageController::class, 'toggleIgnore'])      ->name('pointage.toggle-ignore');
            Route::get('/pointage/{employee}/last-photo',      [PointageController::class, 'lastPhoto'])         ->name('pointage.last-photo')->where('employee', '[0-9]+');
            Route::put('/pointage/{pointage}',                 [PointageController::class, 'update'])            ->name('pointage.update');
            Route::post('/pointage/toggle-absence',            [PointageController::class, 'toggleAbsence'])     ->name('pointage.toggle-absence');
            Route::get('/pointage/export',                     [PointageController::class, 'export'])            ->name('pointage.export');
        });

        // ── Salary — rh + admin
        Route::prefix('salary')->name('salary.')->middleware(['role:admin,rh'])->group(function () {
            Route::get('/',              [SalaryController::class, 'index'])       ->name('index');
            Route::get('/export',        [SalaryController::class, 'export'])      ->name('export');
            Route::get('/export-pdf',    [SalaryController::class, 'exportPdf'])   ->name('export-pdf');
            Route::get('/setting',       [SalaryController::class, 'setting'])     ->name('setting');
            Route::post('/generate-all', [SalaryController::class, 'generateAll']) ->name('generate-all');
            Route::get('/{employee}/create',   [SalaryController::class, 'create'])         ->name('create')  ->where('employee', '[0-9]+');
            Route::post('/{employee}',         [SalaryController::class, 'store'])          ->name('update')  ->where('employee', '[0-9]+');
            Route::patch('/{salary}/validate', [SalaryController::class, 'validateSalary']) ->name('validate')->where('salary', '[0-9]+');
            Route::patch('/{salary}/paid',     [SalaryController::class, 'markPaid'])       ->name('paid')    ->where('salary', '[0-9]+');
            Route::delete('/{salary}',         [SalaryController::class, 'destroy'])        ->name('destroy') ->where('salary', '[0-9]+');
        });

        // ── Absences approve/reject — rh + admin
        Route::prefix('absences')->name('absences.')->middleware(['role:admin,rh'])->group(function () {
            Route::match(['POST', 'PUT'], '/{absence}/approve', [AbsenceController::class, 'approve'])->name('approve');
            Route::match(['POST', 'PUT'], '/{absence}/reject',  [AbsenceController::class, 'reject']) ->name('reject');
        });

        // ── News — rh + admin
        Route::middleware(['role:admin,rh'])->group(function () {
            Route::resource('news', NewsController::class);
        });

        // ── Reporting — rh + admin
        Route::prefix('reporting')->name('reporting.')->middleware(['role:admin,rh'])->group(function () {
            Route::get('/',           [ReportingController::class, 'index'])    ->name('index');
            Route::get('/export-pdf', [ReportingController::class, 'exportPdf'])->name('export-pdf');
            Route::get('/debug',      [ReportingController::class, 'debug'])    ->name('debug');
        });

        // ── GED — rh + admin
        Route::middleware(['role:admin,rh'])->group(function () {
            Route::get('/ged',                     [DocumentController::class, 'index'])   ->name('ged.index');
            Route::post('/ged',                    [DocumentController::class, 'store'])   ->name('ged.store');
            Route::get('/ged/{document}/edit',     [DocumentController::class, 'edit'])    ->name('ged.edit');
            Route::put('/ged/{document}',          [DocumentController::class, 'update'])  ->name('ged.update');
            Route::delete('/ged/{document}',       [DocumentController::class, 'destroy']) ->name('ged.destroy');
            Route::get('/ged/{document}/download', [DocumentController::class, 'download'])->name('ged.download');

            Route::get('/ged/modeles',               [DocumentModeleController::class, 'index'])  ->name('ged.modeles.index');
            Route::post('/ged/modeles',              [DocumentModeleController::class, 'store'])  ->name('ged.modeles.store');
            Route::get('/ged/modeles/{modele}/edit', [DocumentModeleController::class, 'edit'])   ->name('ged.modeles.edit');
            Route::put('/ged/modeles/{modele}',      [DocumentModeleController::class, 'update']) ->name('ged.modeles.update');
            Route::delete('/ged/modeles/{modele}',   [DocumentModeleController::class, 'destroy'])->name('ged.modeles.destroy');
            Route::get('/ged/entete',                [DocumentEnteteController::class, 'index'])  ->name('ged.entete.index');
            Route::post('/ged/entete',               [DocumentEnteteController::class, 'store'])  ->name('ged.entete.store');
        });

        // ── Paramétrage — rh + admin
        Route::middleware(['role:admin,rh'])->group(function () {
            Route::get('/parametrage', [ParametrageController::class, 'index'])->name('parametrage.index');
            Route::post('/parametrage/documents/upload',        [ParametrageController::class, 'uploadDocument'])     ->name('parametrage.documents.upload');
            Route::get('/parametrage/employees/{id}/documents', [ParametrageController::class, 'getEmployeeDocuments']);
            Route::delete('/parametrage/documents/{id}',        [ParametrageController::class, 'deleteDocument']);

            Route::post('/departments',                [DepartmentController::class, 'store'])  ->name('departments.store');
            Route::put('/departments/{department}',    [DepartmentController::class, 'update']) ->name('departments.update');
            Route::delete('/departments/{department}', [DepartmentController::class, 'destroy'])->name('departments.destroy');
        });

        // ── LMS
        Route::middleware(['role:admin,rh,employee'])->prefix('lms')->name('lms.')->group(function () {
            Route::get('/',                        [FormationController::class, 'index'])                ->name('index');
            Route::get('/planning',                [FormationController::class, 'planning'])             ->name('planning');
            Route::post('/',                       [FormationController::class, 'store'])                ->name('store');
            Route::put('/{formation}',             [FormationController::class, 'update'])               ->name('update');
            Route::delete('/{formation}',          [FormationController::class, 'destroy'])              ->name('destroy');
            Route::get('/export-pdf',              [FormationController::class, 'exportPdf'])            ->name('exportPdf');
            Route::get('/employees-by-department', [FormationController::class, 'employeesByDepartment'])->name('employeesByDepartment');
        });

        // ── Référentiel
        Route::middleware(['role:admin,rh'])->prefix('referentiel')->name('referentiel.')->group(function () {
            Route::get('/', [ReferentielController::class, 'index'])->name('index');

            Route::post('/formateurs',               [ReferentielController::class, 'storeFormateur'])  ->name('formateurs.store');
            Route::put('/formateurs/{formateur}',    [ReferentielController::class, 'updateFormateur']) ->name('formateurs.update');
            Route::delete('/formateurs/{formateur}', [ReferentielController::class, 'destroyFormateur'])->name('formateurs.destroy');

            Route::post('/formations',               [ReferentielController::class, 'storeFormation'])  ->name('formations.store');
            Route::put('/formations/{formation}',    [ReferentielController::class, 'updateFormation']) ->name('formations.update');
            Route::delete('/formations/{formation}', [ReferentielController::class, 'destroyFormation'])->name('formations.destroy');

            Route::post('/organismes',               [ReferentielController::class, 'storeOrganisme'])  ->name('organismes.store');
            Route::put('/organismes/{organisme}',    [ReferentielController::class, 'updateOrganisme']) ->name('organismes.update');
            Route::delete('/organismes/{organisme}', [ReferentielController::class, 'destroyOrganisme'])->name('organismes.destroy');

            Route::get('/api/formateurs', [ReferentielController::class, 'formateursActifs'])->name('api.formateurs');
            Route::get('/api/formations', [ReferentielController::class, 'catalogueActif'])  ->name('api.formations');
            Route::get('/api/organismes', [ReferentielController::class, 'organismesActifs'])->name('api.organismes');
        });

Route::middleware(['auth', 'role:admin,rh'])
    ->prefix('admin/codes')
    ->name('admin.codes.')
    ->group(function () {

        Route::get('/', [AdminVerifCodeController::class, 'index'])
            ->name('index');

        Route::get('/stats', [AdminVerifCodeController::class, 'stats'])
            ->name('stats');

        Route::post('/generate-missing', [AdminVerifCodeController::class, 'generateMissing'])
            ->name('generate-missing');

        Route::post('/renew-quarter', [AdminVerifCodeController::class, 'renewQuarter'])
            ->name('renew-quarter');

        Route::post('/force-renew', [AdminVerifCodeController::class, 'forceRenew'])
            ->name('force-renew');

        Route::post('/users/{user}/replace', [AdminVerifCodeController::class, 'replaceForUser'])
            ->name('replace-user');

        Route::delete('/{verificationCode}/revoke', [AdminVerifCodeController::class, 'revoke'])
            ->name('revoke');
    });

 });

// ══ MODULE ÉQUIPEMENTS ══
Route::middleware(['role:admin,rh'])->prefix('equipements')->name('equipements.')->group(function () {
    Route::get('/',                               [\App\Http\Controllers\EquipementController::class, 'index'])        ->name('index');
    Route::get('/export',                         [\App\Http\Controllers\EquipementController::class, 'export'])       ->name('export');
    Route::post('/store',                         [\App\Http\Controllers\EquipementController::class, 'store'])        ->name('store');
    Route::put('/{equipement}',                   [\App\Http\Controllers\EquipementController::class, 'update'])       ->name('update');
    Route::delete('/{equipement}',                [\App\Http\Controllers\EquipementController::class, 'destroy'])      ->name('destroy');
    Route::post('/affecter',                      [\App\Http\Controllers\EquipementController::class, 'affecter'])     ->name('affecter');
    Route::post('/restituer/{affectation}',       [\App\Http\Controllers\EquipementController::class, 'restituer'])    ->name('restituer');
    Route::post('/valider-sortie/{employeeId}',   [\App\Http\Controllers\EquipementController::class, 'validerSortie'])->name('valider_sortie');
    Route::post('/declarer-perte/{affectation}',  [\App\Http\Controllers\EquipementController::class, 'declarerPerte'])->name('declarer_perte');
    Route::post('/signer-decharge/{affectation}', [\App\Http\Controllers\EquipementController::class, 'signerDecharge'])->name('signer_decharge');
    Route::get('/salarie/{employeeId}',           [\App\Http\Controllers\EquipementController::class, 'ficheSalarie']) ->name('fiche_salarie');
    Route::get('/salarie/{employeeId}/pdf',  [\App\Http\Controllers\EquipementController::class, 'ficheSalariePdf'])->name('fiche_salarie.pdf');
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

        // ── Employés — CRUD complet
        Route::get('employees',                               [EmployeeController::class, 'index'])          ->name('employees.index');
        Route::get('employees/create',                        [EmployeeController::class, 'create'])         ->name('employees.create');
        Route::post('employees',                              [EmployeeController::class, 'store'])          ->name('employees.store');
        Route::get('employees/export',                        [EmployeeController::class, 'export'])         ->name('employees.export');
        Route::get('employees/export-pdf',                    [EmployeeController::class, 'exportPdf'])      ->name('employees.export-pdf');
        Route::get('employees/export-pdf-dept/{department}',  [EmployeeController::class, 'exportPdfByDept'])->name('employees.export-pdf-dept');
        Route::post('employees/reorder',                      [EmployeeController::class, 'reorder'])        ->name('employees.reorder');
        Route::get('employees/ajax',                          [EmployeeController::class, 'ajax'])           ->name('employees.ajax');
        Route::post('employees/{employee}/regenerate-pin',    [EmployeeController::class, 'regeneratePin'])  ->name('employees.regeneratePin');
        Route::get('/employees/check-unique',                 [EmployeeController::class, 'checkUnique'])->middleware(['auth', 'identify-tenant'])
    ->name('employees.check-unique');
        Route::resource('employees', EmployeeController::class)->only(['edit', 'update', 'destroy']);

        // ── Planning — écriture
        Route::prefix('planning')->name('planning.')->group(function () {
            Route::get('/global',          [PlanningController::class, 'global'])           ->name('global');
            Route::get('/weekly/export',   [PlanningController::class, 'exportWeekly'])     ->name('weekly.export');
            Route::get('/weekly/pdf',      [PlanningController::class, 'exportWeeklyPdf'])  ->name('weekly.pdf');
            Route::get('/monthly/export',  [PlanningController::class, 'exportMonthly'])    ->name('monthly.export');
            Route::get('/monthly/pdf',     [PlanningController::class, 'exportMonthlyPdf']) ->name('monthly.pdf');
            Route::post('/',               [PlanningController::class, 'store'])             ->name('store');
            Route::put('/{planning}',      [PlanningController::class, 'update'])            ->name('update');
            Route::delete('/{planning}',   [PlanningController::class, 'destroy'])           ->name('destroy');
            Route::post('/drag-drop',      [PlanningController::class, 'updateDragDrop'])    ->name('dragDrop');
            Route::post('/update-room',    [PlanningController::class, 'updateRoom'])        ->name('update.room');
            Route::prefix('templates')->name('templates.')->group(function () {
                Route::get('/',              [WeekTemplateController::class, 'index'])     ->name('index');
                Route::get('/create',        [WeekTemplateController::class, 'create'])    ->name('create');
                Route::post('/',             [WeekTemplateController::class, 'store'])     ->name('store');
                Route::delete('/{template}', [WeekTemplateController::class, 'destroy'])  ->name('destroy');
                Route::get('/apply',         [WeekTemplateController::class, 'applyForm'])->name('apply');
                Route::post('/apply',        [WeekTemplateController::class, 'apply'])    ->name('apply.post');
            });
        });

        Route::get('/rooms',           [RoomController::class, 'index'])  ->name('rooms.index');
        Route::post('/rooms',          [RoomController::class, 'store'])  ->name('rooms.store');
        Route::put('/rooms/{room}',    [RoomController::class, 'update']) ->name('rooms.update');
        Route::delete('/rooms/{room}', [RoomController::class, 'destroy'])->name('rooms.destroy');

        Route::get('/salary/settings', [PayrollSettingController::class, 'index']) ->name('payroll.settings');
        Route::put('/salary/settings', [PayrollSettingController::class, 'update'])->name('payroll.settings.update');

        Route::prefix('variables')->name('variables.')->group(function () {
            Route::get('/',                     [VariableElementController::class, 'index'])  ->name('index');
            Route::post('/',                    [VariableElementController::class, 'store'])  ->name('store');
            Route::delete('/{variableElement}', [VariableElementController::class, 'destroy'])->name('destroy');
        });

        Route::prefix('api')->group(function () {
            Route::get('/dashboard/stats', [DashboardController::class, 'stats']);
            Route::get('/dashboard/data',  [DashboardController::class, 'data']);
            Route::get('/planning/events', [PlanningController::class,  'events']);
        });

    }); // fin admin

}); // fin tenant

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
        Route::post('/logout',   [BadgeAuthController::class,     'logout'])       ->name('logout');
        Route::get('/dashboard', [BadgeDashboardController::class, 'index'])       ->name('dashboard');
        Route::post('/entree',   [BadgePointageController::class,  'entree'])      ->name('entree');
        Route::post('/sortie',   [BadgePointageController::class,  'sortie'])      ->name('sortie');
        Route::post('/action',   [BadgePointageController::class,  'handleAction'])->name('action');
        Route::get('/result',    [BadgePointageController::class,  'result'])      ->name('result');
        Route::post('/geo/save', [BadgeAuthController::class,      'saveGeo'])     ->name('geo.save');
    });
});
Route::middleware(['auth'])->group(function () {
    Route::get('/parametrage/site-location',         [\App\Http\Controllers\SiteLocationController::class, 'index'])  ->name('site-location.index');
    Route::post('/parametrage/site-location',        [\App\Http\Controllers\SiteLocationController::class, 'store'])  ->name('site-location.store');
    Route::delete('/parametrage/site-location/{id}', [\App\Http\Controllers\SiteLocationController::class, 'destroy'])->name('site-location.destroy');
});
Route::middleware(['web', 'auth', 'identify-tenant', '2fa'])
    ->prefix('notes-frais')
    ->name('expenses.')
    ->group(function () {
        Route::get('/', [ExpenseController::class, 'index'])->name('index');
        Route::get('/nouvelle', [ExpenseController::class, 'create'])->name('create');
        Route::post('/', [ExpenseController::class, 'store'])->name('store');

        Route::get('/{expense}/modifier', [ExpenseController::class, 'edit'])->name('edit');
        Route::put('/{expense}', [ExpenseController::class, 'update'])->name('update');
        Route::post('/{expense}/approve', [ExpenseController::class, 'approve'])->name('approve');
        Route::post('/{expense}/reject', [ExpenseController::class, 'reject'])->name('reject');
        Route::delete('/{expense}', [ExpenseController::class, 'destroy'])->name('destroy');
        Route::get('/export', [ExpenseController::class, 'exportCsv'])->name('export');
        Route::get('/export-pdf', [ExpenseController::class, 'exportPdf'])->name('export.pdf');

        // Anti-abus : limite le nombre d'appels OCR par utilisateur (l'API externe est facturée).
        Route::post('/ocr/scan', [ExpenseOCRController::class, 'scan'])
            ->middleware('throttle:20,1')
            ->name('ocr.scan');
    });
Route::middleware(['auth'])->group(function () {

    // ═══════════════════════════════════════════════════════════════════
    // ESPACE EMPLOYÉ — 2 onglets
    // ═══════════════════════════════════════════════════════════════════
    Route::prefix('mes-activites')->name('activites.')->group(function () {

        // Onglet 1 : Mes tâches
        Route::get('/mes-taches', [MyTaskController::class, 'index'])->name('my-tasks.index');
        Route::post('/mes-taches', [MyTaskController::class, 'store'])->name('my-tasks.store');
        Route::patch('/mes-taches/{task}', [MyTaskController::class, 'update'])->name('my-tasks.update');

        // Onglet 2 : Saisie de temps (fonctionnalité principale du module)
        Route::get('/saisie-temps', [TimeEntryController::class, 'index'])->name('time-entries.index');
        Route::post('/saisie-temps', [TimeEntryController::class, 'store'])->name('time-entries.store');
        Route::delete('/saisie-temps/{activity}', [TimeEntryController::class, 'destroy'])->name('time-entries.destroy');
        Route::get('/saisie-temps/projets/{project}/taches', [TimeEntryController::class, 'tasksForProject'])->name('time-entries.tasks-for-project');
    });

    // ═══════════════════════════════════════════════════════════════════
    // ESPACE ADMIN / RH — 3 onglets
    // ═══════════════════════════════════════════════════════════════════
    Route::prefix('admin/activites')->name('activites.admin.')->middleware(['role:admin,rh'])->group(function () {

        // Onglet 1 : État d'avancement
        Route::get('/', [ActivitesAdminDashboardController::class, 'index'])->name('dashboard');

        // Onglet 2 : Projets
        Route::get('/projets', [ActivitesAdminProjectController::class, 'index'])->name('projects.index');
        Route::get('/projets/export-pdf', [ActivitesAdminProjectController::class, 'exportPdf'])->name('projects.export-pdf');
        Route::get('/projets/export-excel', [ActivitesAdminProjectController::class, 'exportExcel'])->name('projects.export-excel');
        Route::post('/projets', [ActivitesAdminProjectController::class, 'store'])->name('projects.store');
        Route::get('/projets/{project}', [ActivitesAdminProjectController::class, 'show'])->name('projects.show');
        Route::put('/projets/{project}', [ActivitesAdminProjectController::class, 'update'])->name('projects.update');
        Route::delete('/projets/{project}', [ActivitesAdminProjectController::class, 'destroy'])->name('projects.destroy');

        // Onglet 3 : Tâches
        Route::get('/taches', [ActivitesAdminTaskController::class, 'index'])->name('tasks.index');
        Route::get('/taches/export-pdf', [ActivitesAdminTaskController::class, 'exportPdf'])->name('tasks.export-pdf');
        Route::get('/taches/export-excel', [ActivitesAdminTaskController::class, 'exportExcel'])->name('tasks.export-excel');
        Route::post('/taches', [ActivitesAdminTaskController::class, 'store'])->name('tasks.store');
        Route::get('/taches/{task}', [ActivitesAdminTaskController::class, 'show'])->name('tasks.show');
        Route::put('/taches/{task}', [ActivitesAdminTaskController::class, 'update'])->name('tasks.update');
        Route::delete('/taches/{task}', [ActivitesAdminTaskController::class, 'destroy'])->name('tasks.destroy');
    });
});
