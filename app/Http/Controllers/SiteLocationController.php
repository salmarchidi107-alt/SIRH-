<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class SiteLocationController extends Controller
{
    // =========================================================================
    // getCurrentTenantId — côté serveur uniquement, jamais depuis le client
    // =========================================================================
    private function getCurrentTenantId(): mixed
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

    // =========================================================================
    // index — liste toutes les localisations du tenant courant (JSON)
    // =========================================================================
    public function index(): JsonResponse
    {
        $tenantId = $this->getCurrentTenantId();

        if (! $tenantId) {
            return response()->json(['success' => false, 'message' => 'Tenant non identifié.'], 403);
        }

        $locations = DB::table('site_locations')
            ->where('tenant_id', $tenantId)
            ->orderBy('department')
            ->get();

        return response()->json([
            'success'   => true,
            'locations' => $locations,
        ]);
    }

    // =========================================================================
    // store — crée une nouvelle localisation pour le tenant courant
    // =========================================================================
    public function store(Request $request): JsonResponse
    {
        $tenantId = $this->getCurrentTenantId();

        if (! $tenantId) {
            return response()->json(['success' => false, 'message' => 'Tenant non identifié.'], 403);
        }

        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'site_name'     => 'required|string|max:255',
            'department'    => 'nullable|string|max:255',
            'latitude'      => 'required|numeric|between:-90,90',
            'longitude'     => 'required|numeric|between:-180,180',
            'radius_meters' => 'required|integer|min:50|max:5000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => collect($validator->errors()->all())->implode(' / '),
            ], 422);
        }

        $data = $validator->validated();

        // Un seul enregistrement par département (ou par "tous") par tenant
        $existing = DB::table('site_locations')
            ->where('tenant_id', $tenantId)
            ->where('department', $data['department'] ?? null)
            ->first();

        if ($existing) {
            // On met à jour au lieu de créer un doublon
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

            return response()->json([
                'success'  => true,
                'message'  => 'Localisation mise à jour.',
                'location' => $loc,
            ]);
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

        return response()->json([
            'success'  => true,
            'message'  => 'Localisation créée.',
            'location' => $loc,
        ]);
    }

    // =========================================================================
    // destroy — supprime une localisation du tenant courant
    // =========================================================================
    public function destroy(int $id): JsonResponse
    {
        $tenantId = $this->getCurrentTenantId();

        if (! $tenantId) {
            return response()->json(['success' => false, 'message' => 'Tenant non identifié.'], 403);
        }

        // On s'assure que l'enregistrement appartient bien au tenant courant
        $deleted = DB::table('site_locations')
            ->where('id', $id)
            ->where('tenant_id', $tenantId)
            ->delete();

        if (! $deleted) {
            return response()->json(['success' => false, 'message' => 'Localisation introuvable.'], 404);
        }

        $this->clearCache($tenantId);

        return response()->json(['success' => true, 'message' => 'Localisation supprimée.']);
    }

    // =========================================================================
    // clearCache — invalide le cache des localisations du tenant
    // =========================================================================
    private function clearCache(mixed $tenantId): void
    {
        Cache::forget("tenant_site_locations_{$tenantId}");
    }
}
