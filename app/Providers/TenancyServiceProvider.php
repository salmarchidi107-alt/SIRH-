<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Stancl\Tenancy\Events\TenantCreated;

class TenancyServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot()
    {
        // Single DB - no automatic DB creation/migrations
    }

    // Removed: No DB jobs for single database setup
}
