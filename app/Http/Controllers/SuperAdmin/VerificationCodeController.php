<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\{Tenant, User, VerificationCode};
use App\Services\VerificationCodeService;
use Illuminate\Http\{Request, JsonResponse};

class VerificationCodeController extends Controller
{
    public function __construct(
        private readonly VerificationCodeService $service
    ) {}

    public function index(Request $request)
    {
        $query = VerificationCode::with(['user', 'assignedBy', 'revokedBy'])->latest();

        if ($request->filled('tenant_id')) $query->forTenant($request->tenant_id);
        if ($request->filled('status'))    $query->where('status', $request->status);

        $codes   = $query->paginate(100)->withQueryString();
        $tenants = Tenant::orderBy('name')->get();

        $dashboardStats = [];
        foreach ($tenants as $t) {
            $dashboardStats[$t->id] = $this->service->dashboardStats($t->id);
        }

        return view('superadmin.codes.index', compact('codes', 'tenants', 'dashboardStats'));
    }

    public function tenantStats(string $tenantId): JsonResponse
    {
        $tenant = Tenant::findOrFail($tenantId);
        $stats  = $this->service->dashboardStats($tenantId);

        return response()->json([
            'success'     => true,
            'tenant_name' => $tenant->name ?? $tenantId,
            'stats'       => $stats,
        ]);
    }

    public function generateMissing(Request $request): JsonResponse
    {
        $request->validate([
            'tenant_id' => ['required', 'string', 'exists:tenants,id'],
        ]);

        try {
            $result = $this->service->generateMissing(
                $request->tenant_id,
                auth()->id()
            );

            $msg = "{$result['generated']} code(s) générés et attribués automatiquement.";

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'result'  => $result,
                    'message' => $msg,
                    'stats'   => $this->service->dashboardStats($request->tenant_id),
                ]);
            }

            return back()->with('success', $msg);

        } catch (\DomainException $e) {
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
            }
            return back()->withErrors(['generate' => $e->getMessage()]);
        }
    }

    public function replaceForUser(Request $request, User $user): JsonResponse
    {
        try {
            $new = $this->service->replaceForUser($user, auth()->id());

            return response()->json([
                'success'  => true,
                'new_code' => $new->code,
                'new_id'   => $new->id,
                'message'  => 'Nouveau code attribué à ' . $user->name . '.',
            ]);
        } catch (\DomainException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    public function revoke(Request $request, VerificationCode $verificationCode): mixed
    {
        $request->validate([
            'reason' => ['nullable', 'string', 'max:255'],
        ]);

        try {
            $this->service->revoke(
                $verificationCode,
                auth()->id(),
                (string) ($request->input('reason') ?? '')
            );

            if ($request->expectsJson()) {
                return response()->json(['success' => true, 'message' => 'Code révoqué.']);
            }

            return back()->with('success', 'Code révoqué avec succès.');

        } catch (\DomainException $e) {
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
            }
            return back()->withErrors(['revoke' => $e->getMessage()]);
        }
    }
}
