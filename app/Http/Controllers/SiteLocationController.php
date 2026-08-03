<?php

namespace App\Http\Controllers;

use App\Services\SiteLocationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SiteLocationController extends Controller
{
    public function __construct(private SiteLocationService $siteLocationService) {}

    public function index(): JsonResponse
    {
        $tenantId = $this->siteLocationService->getCurrentTenantId();

        if (! $tenantId) {
            return response()->json(['success' => false, 'message' => 'Tenant non identifié.'], 403);
        }

        return response()->json([
            'success'   => true,
            'locations' => $this->siteLocationService->getLocations($tenantId),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $tenantId = $this->siteLocationService->getCurrentTenantId();

        if (! $tenantId) {
            return response()->json(['success' => false, 'message' => 'Tenant non identifié.'], 403);
        }

        $validator = Validator::make($request->all(), [
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

        $result = $this->siteLocationService->saveLocation($tenantId, $validator->validated());

        return response()->json([
            'success'  => true,
            'message'  => $result['created'] ? 'Localisation créée.' : 'Localisation mise à jour.',
            'location' => $result['location'],
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        $tenantId = $this->siteLocationService->getCurrentTenantId();

        if (! $tenantId) {
            return response()->json(['success' => false, 'message' => 'Tenant non identifié.'], 403);
        }

        if (! $this->siteLocationService->deleteLocation($tenantId, $id)) {
            return response()->json(['success' => false, 'message' => 'Localisation introuvable.'], 404);
        }

        return response()->json(['success' => true, 'message' => 'Localisation supprimée.']);
    }
}
