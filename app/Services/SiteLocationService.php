<?php

namespace App\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SiteLocationService
{
    public function getCurrentTenantId(): mixed
    {
        $authTenantId   = auth()->check() ? auth()->user()->tenant_id : null;
        $configTenantId = config('app.current_tenant_id');

        if (filled($authTenantId) && filled($configTenantId) && (string) $authTenantId !== (string) $configTenantId) {
            Log::warning('SiteLocation: incohérence tenant_id', [
                'auth'   => $authTenantId,
                'config' => $configTenantId,
                'user'   => auth()->id(),
            ]);
        }

        return filled($authTenantId) ? $authTenantId : (filled($configTenantId) ? $configTenantId : null);
    }

    public function getLocations(mixed $tenantId): Collection
    {
        return DB::table('site_locations')
            ->where('tenant_id', $tenantId)
            ->orderBy('department')
            ->get();
    }

    /**
     * Crée la localisation ou met à jour celle existante pour ce
     * tenant/département (un seul enregistrement par département, ou par
     * "tous" si department est null).
     *
     * @return array{created: bool, location: object}
     */
    public function saveLocation(mixed $tenantId, array $data): array
    {
        $existing = DB::table('site_locations')
            ->where('tenant_id', $tenantId)
            ->where('department', $data['department'] ?? null)
            ->first();

        if ($existing) {
            DB::table('site_locations')
                ->where('id', $existing->id)
                ->update([
                    'site_name'     => $data['site_name'],
                    'latitude'      => round((float) $data['latitude'], 7),
                    'longitude'     => round((float) $data['longitude'], 7),
                    'radius_meters' => (int) $data['radius_meters'],
                    'updated_at'    => now(),
                ]);

            $loc = DB::table('site_locations')->find($existing->id);
            $this->clearCache($tenantId);

            return ['created' => false, 'location' => $loc];
        }

        $id = DB::table('site_locations')->insertGetId([
            'tenant_id'     => $tenantId,
            'department'    => $data['department'] ?? null,
            'site_name'     => $data['site_name'],
            'latitude'      => round((float) $data['latitude'], 7),
            'longitude'     => round((float) $data['longitude'], 7),
            'radius_meters' => (int) $data['radius_meters'],
            'created_at'    => now(),
            'updated_at'    => now(),
        ]);

        $loc = DB::table('site_locations')->find($id);
        $this->clearCache($tenantId);

        return ['created' => true, 'location' => $loc];
    }


    public function deleteLocation(mixed $tenantId, int $id): bool
    {
        $deleted = DB::table('site_locations')
            ->where('id', $id)
            ->where('tenant_id', $tenantId)
            ->delete();

        if ($deleted) {
            $this->clearCache($tenantId);
        }

        return (bool) $deleted;
    }

    private function clearCache(mixed $tenantId): void
    {
        Cache::forget("tenant_site_locations_{$tenantId}");
    }
}
