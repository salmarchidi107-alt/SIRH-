<?php

namespace App\Providers;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        \Illuminate\Support\Facades\Schema::defaultStringLength(191);

        \App\Models\Employee::observe(\App\Observers\EmployeeObserver::class);
        \App\Models\Absence::observe(\App\Observers\AbsenceObserver::class);
        \App\Models\Pointage::observe(\App\Observers\PointageObserver::class);

        // Share cached holidays globally (eliminates per-page API calls)
        view()->composer('*', function ($view) {
            $view->with('globalHolidays', app(\App\Services\HolidayService::class)->getCurrentYearHolidays());
        });

        // ── Directives Blade pour les permissions ─────────────────────────
        $this->registerPermissionDirectives();
    }

    /**
     * Enregistre les directives Blade pour les permissions modules.
     *
     * Utilisation dans les vues :
     *
     *   @canModule('employees', 'create')
     *       <a href="…">Ajouter un employé</a>
     *   @endcanModule
     *
     *   @canView('salary')
     *       <p>Vous pouvez voir les salaires</p>
     *   @endcanView
     *
     *   @canCreate('planning')  … @endcanCreate
     *   @canEdit('absences')    … @endcanEdit
     *   @canDelete('employees') … @endcanDelete
     */
    private function registerPermissionDirectives(): void
    {
        // ── Directive générique ───────────────────────────────────────────
        Blade::directive('canModule', function (string $expression) {
            // Expression attendue : 'module', 'action'
            return "<?php if(auth()->check() && auth()->user()->hasPermission({$expression})): ?>";
        });

        Blade::directive('endcanModule', function () {
            return '<?php endif; ?>';
        });

        // ── Directives par action ─────────────────────────────────────────
        $actions = [
            'View'   => 'canView',
            'Create' => 'canCreate',
            'Edit'   => 'canEdit',
            'Delete' => 'canDelete',
        ];

        foreach ($actions as $pascal => $method) {
            // @canView('salary')
            Blade::directive("can{$pascal}", function (string $module) use ($method) {
                return "<?php if(auth()->check() && auth()->user()->{$method}({$module})): ?>";
            });

            // @endcanView
            Blade::directive("endcan{$pascal}", function () {
                return '<?php endif; ?>';
            });
        }
    }
}
