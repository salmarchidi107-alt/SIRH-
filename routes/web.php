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
use App\Models\User;
use App\Models\Employee;
Route::get('/dashboard', [DashboardController::class, 'dashboard'])->middleware('auth');
Route::get('/actualites', [NewsController::class, 'index']);
Route::get('/holidays/debug', [App\Http\Controllers\HolidayController::class, 'debug']);
Route::get('/holidays/{year}/{month}', [App\Http\Controllers\HolidayController::class, 'index']);
Route::middleware(['auth'])->group(function () {
    Route::get('/temps/vue-ensemble', [VueEnsembleController::class, 'index'])
        ->name('temps.vue-ensemble');
});


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

    if ($linked > 0) {
        echo "<br>Successfully linked {$linked} user(s)!";
    } else {
        echo "No new links were made.";
    }
});


Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login']);

Route::post('/logout', [AuthController::class, 'logout'])->name('logout');


Route::middleware(['auth'])->group(function () {
    Route::get('/', function () {
        return redirect()->route('dashboard');
    });


    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');


    Route::get('/profile', [ProfileController::class, 'index'])->name('profile');


Route::resource('news', NewsController::class);


Route::resource('employees', EmployeeController::class);
Route::get('/trombinoscope', [TrombinoscopeController::class, 'index'])->name('trombinoscope');



Route::get('/planning/weekly', [PlanningController::class, 'weekly'])->name('planning.weekly');
Route::get('/planning/global', [PlanningController::class, 'global'])->name('planning.global');
Route::get('/planning/monthly', [PlanningController::class, 'monthly'])->name('planning.monthly');
Route::get('/planning/show/{employee?}', function() { return redirect()->route('planning.weekly'); })->name('planning.show');
Route::post('/planning', [PlanningController::class, 'store'])->name('planning.store');
Route::put('/planning/{planning}', [PlanningController::class, 'update'])->name('planning.update');
Route::delete('/planning/{planning}', [PlanningController::class, 'destroy'])->name('planning.destroy');
Route::post('/planning/drag-drop', [PlanningController::class, 'updateDragDrop'])->name('planning.dragDrop');


Route::get('/planning/templates', [WeekTemplateController::class, 'index'])->name('planning.templates.index');
Route::get('/planning/templates/create', [WeekTemplateController::class, 'create'])->name('planning.templates.create');
Route::post('/planning/templates', [WeekTemplateController::class, 'store'])->name('planning.templates.store');
Route::delete('/planning/templates/{template}', [WeekTemplateController::class, 'destroy'])->name('planning.templates.destroy');
Route::get('/planning/templates/apply', [WeekTemplateController::class, 'applyForm'])->name('planning.templates.apply');
Route::post('/planning/templates/apply', [WeekTemplateController::class, 'apply'])->name('planning.templates.apply');





Route::prefix('absences')->name('absences.')->group(function () {
    Route::get('/', [AbsenceController::class, 'index'])->name('index');
    Route::get('/create', [AbsenceController::class, 'create'])->name('create');
    Route::post('/', [AbsenceController::class, 'store'])->name('store');


    Route::get('/calendar', [AbsenceController::class, 'calendar'])->name('calendar');
    Route::get('/counters', [AbsenceController::class, 'counters'])->name('counters');

    Route::get('/{absence}', [AbsenceController::class, 'show'])->name('show');
    Route::get('/{absence}/edit', [AbsenceController::class, 'edit'])->name('edit');
    Route::put('/{absence}', [AbsenceController::class, 'update'])->name('update');
    Route::delete('/{absence}', [AbsenceController::class, 'destroy'])->name('destroy');
    Route::post('/{absence}/approve', [AbsenceController::class, 'approve'])->name('approve');
    Route::post('/{absence}/reject', [AbsenceController::class, 'reject'])->name('reject');
});



Route::get('/salary', [SalaryController::class, 'index'])->name('salary.index');
Route::get('/salary/{employee}/create', [SalaryController::class, 'create'])->name('salary.create');

Route::get('/salary/{employee}', [SalaryController::class, 'show'])->name('salary.show');
Route::post('/salary/{employee}', [SalaryController::class, 'update'])->name('salary.update');


Route::prefix('api')->group(function () {
    Route::get('/dashboard/stats', [DashboardController::class, 'stats']);
    Route::get('/planning/events', [PlanningController::class, 'events']);
    Route::get('/notifications/data', [NotificationController::class, 'data'])->name('api.notifications.data');
});


Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
});


// ============================================================
// AJOUTER CES ROUTES DANS votre fichier routes/web.php
// ============================================================


use App\Http\Controllers\VariableElementController;

// ─── Module Paie ─────────────────────────────────────────────

Route::prefix('salary')->name('salary.')->group(function () {

    // Liste des employés + état paie du mois
    Route::get('/', [SalaryController::class, 'index'])->name('index');

    // Générer la paie pour tous les employés d'un mois
    Route::post('/generate-all', [SalaryController::class, 'generateAll'])->name('generate-all');

    // Fiche d'un employé
    Route::get('/{employee}', [SalaryController::class, 'show'])->name('show');

    // Générer / recalculer le bulletin d'un employé
    Route::post('/{employee}', [SalaryController::class, 'store'])->name('update');

    // Valider un bulletin
    Route::patch('/{salary}/validate', [SalaryController::class, 'validateSalary'])->name('validate');

    // Marquer comme payé
    Route::patch('/{salary}/paid', [SalaryController::class, 'markPaid'])->name('paid');

    // Supprimer un bulletin (draft seulement)
    Route::delete('/{salary}', [SalaryController::class, 'destroy'])->name('destroy');

    // Télécharger le PDF
    Route::get('/{salary}/pdf', [SalaryController::class, 'pdf'])->name('pdf');
});

// ─── Payroll Settings ────────────────────────────────────────
Route::get('/salary/settings', [\App\Http\Controllers\PayrollSettingController::class, 'index'])->name('payroll.settings');
Route::put('/salary/settings', [\App\Http\Controllers\PayrollSettingController::class, 'update'])->name('payroll.settings.update');

// ─── Éléments variables ───────────────────────────────────────

Route::prefix('variables')->name('variables.')->group(function () {
    Route::get('/',                         [VariableElementController::class, 'index'])->name('index');
    Route::post('/',                        [VariableElementController::class, 'store'])->name('store');
    Route::delete('/{variableElement}',     [VariableElementController::class, 'destroy'])->name('destroy');
});
