<?php

namespace App\Providers;

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

        // Share cached holidays globally (eliminates per-page API calls)
        view()->composer('*', function ($view) {
            $view->with('globalHolidays', app(\App\Services\HolidayService::class)->getCurrentYearHolidays());
        });
    }
}
