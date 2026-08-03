<?php

declare(strict_types=1);

namespace App\Providers;

use App\Services\OCR\OCRManager;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(OCRManager::class, function ($app) {
            return new OCRManager(
                // 'ocrspace' est désormais le seul driver disponible (Azure et
                // Google Document AI ont été retirés). Le paramètre reste lu
                // depuis .env pour permettre l'ajout futur d'un autre fournisseur
                // sans modifier de code.
                defaultDriver: config('services.ocr.driver', 'ocrspace'),
            );
        });
    }

    public function boot(): void
    {
        \Illuminate\Support\Facades\Schema::defaultStringLength(191);

        \App\Models\Employee::observe(\App\Observers\EmployeeObserver::class);
    }
}
