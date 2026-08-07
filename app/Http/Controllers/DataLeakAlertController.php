<?php

namespace App\Http\Controllers;

use App\Models\DataLeakAlert;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class DataLeakAlertController extends Controller
{

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'leaked_tenant_id' => 'nullable',
            'module'           => 'nullable|string|max:100',
            'rows_count'       => 'nullable|integer|min:0',
            'row_ids'          => 'nullable|array',
            'row_ids.*'        => 'nullable',
        ]);

        $user  = $request->user();
        $route = $request->route();

        $leakedTenantId   = $validated['leaked_tenant_id'] ?? null;
        $leakedTenantName = $leakedTenantId
            ? optional(Tenant::find($leakedTenantId))->name
            : null;

        DataLeakAlert::create([
            'user_id'              => $user?->id,
            'user_name'            => $user?->name,
            'user_email'           => $user?->email,
            'expected_tenant_id'   => $user?->tenant_id,
            'expected_tenant_name' => optional($user?->tenant)->name,
            'leaked_tenant_id'     => $leakedTenantId,
            'leaked_tenant_name'   => $leakedTenantName,
            'module'               => $validated['module'] ?? null,
            'route_name'           => $route?->getName(),
            'controller_action'    => $route?->getActionName(),
            'url'                  => $request->headers->get('referer') ?? $request->fullUrl(),
            'rows_count'           => $validated['rows_count'] ?? 0,
            'row_ids'              => $validated['row_ids'] ?? [],
            'ip_address'           => $request->ip(),
            'user_agent'           => $request->userAgent(),
        ]);

        return response()->json(['success' => true], 201);
    }

    /**
     * Liste technique pour le SuperAdmin, avec filtres.
     */
    public function index(Request $request)
    {
        $tenants = \App\Models\Tenant::orderBy('name')->get(['id', 'name']);
        $alerts = DataLeakAlert::query()
            ->when($request->filled('tenant'), function ($q) use ($request) {
                $q->where('expected_tenant_id', $request->tenant)
                  ->orWhere('leaked_tenant_id', $request->tenant);
            })
            ->when($request->filled('module'), fn ($q) => $q->where('module', $request->module))
            ->when($request->filled('user'), fn ($q) => $q->where('user_email', 'like', '%' . $request->user . '%'))
            ->when($request->filled('date_from'), fn ($q) => $q->whereDate('created_at', '>=', $request->date_from))
            ->when($request->filled('date_to'), fn ($q) => $q->whereDate('created_at', '<=', $request->date_to))
            ->latest()
            ->paginate(30)
            ->withQueryString();

        return view('superadmin.data-leak-alerts.index', compact('alerts', 'tenants'));
    }
}
