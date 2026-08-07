<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('tenants') || !Schema::hasColumn('tenants', 'data')) {
            return;
        }

        $tenants = DB::table('tenants')
            ->where(function ($q) {
                $q->whereNull('data')
                  ->orWhereRaw('data = "{}"')
                  ->orWhereRaw('data = "[]"');
            })
            ->get();

        foreach ($tenants as $tenant) {
            $currentData = [];
            try {
                $currentData = json_decode($tenant->data ?? '{}', true) ?: [];
            } catch (\Throwable $e) {}

            $newData = array_filter([
                'brand_color' => $currentData['brand_color'] ?? '#1a8fa5',
                'region'      => $currentData['region']      ?? 'EU-West',
                'sector'      => $currentData['sector']      ?? null,
                'logo_path'   => $currentData['logo_path']   ?? null,
            ], fn($v) => !is_null($v));

            DB::table('tenants')
                ->where('id', $tenant->id)
                ->update(['data' => json_encode(array_merge($currentData, $newData))]);
        }
    }

    public function down(): void
    {
        // Pas de rollback destructif
    }
};
