<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use App\Models\Tenant;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Normalize tenants with NULL or empty data JSON from legacy columns
        Tenant::whereNull('data')
            ->orWhereRaw('JSON_LENGTH(data) = 0 OR data = "{}" OR data = "[]"')
            ->chunk(50, function ($tenants) {
                foreach ($tenants as $tenant) {
                    $attributes = $tenant->getAttributes();
                    $newData = array_filter([
                        'name' => $attributes['name'] ?? null,
                        'slug' => $attributes['slug'] ?? null,
                        'plan' => $attributes['plan'] ?? 'starter',
                        'status' => $attributes['status'] ?? 'inactive',
                        'brand_color' => data_get($tenant->data, 'brand_color', '#1a8fa5'),
                        'sector' => data_get($tenant->data, 'sector'),
                        'logo_path' => data_get($tenant->data, 'logo_path'),
                        'region' => data_get($tenant->data, 'region', 'EU-West'),
                    ], fn($v) => !is_null($v));

                    $tenant->update(['data' => array_merge((array)($tenant->data ?? []), $newData)]);
                }
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No destructive rollback - normalization is safe forward-only
    }
};

