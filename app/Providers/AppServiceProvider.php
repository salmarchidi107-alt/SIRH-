<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Event;
use Carbon\Carbon;
use Stancl\Tenancy\Events\TenantCreated;
use App\Listeners\CreateTenantDatabase;

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
    public function boot(): void {
        // Chemin migrations landlord
        $this->loadMigrationsFrom(database_path('migrations/landlord'));
    }
}


