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

    // ─── Index ────────────────────────────────────────────────────────────────

    public function index(Request $request)
    {
        $query = VerificationCode::with(['user', 'assignedBy', 'revokedBy'])->latest();

        if ($request->filled('tenant_id')) $query->forTenant($request->tenant_id);
        if ($request->filled('status'))    $query->where('status', $request->status);
        if ($request->filled('quarter'))   $query->forQuarter($request->quarter);

        $codes   = $query->paginate(100)->withQueryString();
        $tenants = Tenant::orderBy('name')->get();

        // Stats par tenant pour le dashboard
        $dashboardStats = [];
        foreach ($tenants as $t) {
            $dashboardStats[$t->id] = $this->service->dashboardStats($t->id);
        }

        return view('superadmin.codes.index', compact('codes', 'tenants', 'dashboardStats'));
    }

    // ─── Stats AJAX ──────────────────────────────────────────────────────────

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

    // ─── Génération des codes manquants ──────────────────────────────────────

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

    // ─── Renouvellement trimestriel ──────────────────────────────────────────

    public function renewQuarter(Request $request): JsonResponse
    {
        $request->validate([
            'tenant_id' => ['required', 'string', 'exists:tenants,id'],
        ]);

        try {
            $result = $this->service->renewQuarter(
                $request->tenant_id,
                auth()->id()
            );

            $msg = "Trimestre {$result['quarter']} renouvelé : {$result['expired']} expiré(s), {$result['generated']} généré(s).";

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
            return back()->withErrors(['renew' => $e->getMessage()]);
        }
    }

    public function forceRenew(Request $request): JsonResponse
{
    $request->validate([
        'tenant_id' => ['required', 'string', 'exists:tenants,id'],
    ]);

    try {
        $result = $this->service->forceRenewCurrentQuarter(
            $request->tenant_id,
            auth()->id()
        );

        $msg = "Renouvellement forcé {$result['quarter']} : "
             . "{$result['revoked']} révoqué(s), {$result['generated']} généré(s).";

        return response()->json([
            'success' => true,
            'result'  => $result,
            'message' => $msg,
        ]);

    } catch (\DomainException $e) {
        return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
    }
}

    // ─── Remplacement individuel ─────────────────────────────────────────────

    /**
     * IMPORTANT : Laravel injecte $user via route model binding (User::findOrFail).
     * L'utilisateur est donc toujours existant — jamais un nouvel objet sans ID.
     * C'est ce qui garantit qu'on ne crée pas un doublon d'utilisateur.
     */
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

    // ─── Révocation ──────────────────────────────────────────────────────────

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
