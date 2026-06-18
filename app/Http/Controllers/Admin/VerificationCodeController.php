<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\{VerificationCode, User};
use App\Services\VerificationCodeService;
use Illuminate\Http\{Request, JsonResponse};

/**
 * Gestion des codes 2FA pour l'espace Admin.
 *
 * ─── Principe de délégation ────────────────────────────────────────────────
 * Ce contrôleur ne contient AUCUNE logique métier. Tout est délégué au
 * VerificationCodeService déjà validé dans le module SuperAdmin.
 * La seule responsabilité de cette classe est de :
 *   1. Forcer le tenant_id de l'utilisateur connecté sur chaque opération.
 *   2. Vérifier que les ressources manipulées appartiennent bien à ce tenant.
 *   3. Construire la réponse (vue ou JSON) adaptée à l'espace Admin.
 *
 * ─── Sécurité ───────────────────────────────────────────────────────────────
 * • Toutes les routes sont protégées par le middleware 'role:admin,rh' (routes/web.php).
 * • Le tenant_id est TOUJOURS lu depuis auth()->user()->tenant_id — jamais depuis
 *   la requête — ce qui garantit qu'un admin ne peut pas agir sur un autre tenant.
 * • Les model bindings (VerificationCode, User) sont vérifiés via assertBelongsToTenant()
 *   avant toute opération.
 */
class VerificationCodeController extends Controller
{
    public function __construct(
        private readonly VerificationCodeService $service
    ) {}

    // ─── Helpers ─────────────────────────────────────────────────────────────

    /** Retourne le tenant_id de l'admin connecté — source unique de vérité. */
    private function tenantId(): string
    {
        return (string) auth()->user()->tenant_id;
    }

    /**
     * Vérifie qu'une ressource appartient au tenant de l'admin connecté.
     * Lève une 403 si ce n'est pas le cas (protection contre l'IDOR).
     */
    private function assertBelongsToTenant(string $resourceTenantId): void
    {
        abort_unless(
            $resourceTenantId === $this->tenantId(),
            403,
            'Accès refusé : cette ressource n\'appartient pas à votre tenant.'
        );
    }

    // ─── Index ────────────────────────────────────────────────────────────────

    public function index(Request $request)
    {
        $tenantId = $this->tenantId();
        $stats    = $this->service->dashboardStats($tenantId);

        // Tous les codes du tenant — 1 ligne par utilisateur (logique identique au SuperAdmin)
        $allCodes = VerificationCode::with(['user', 'assignedBy', 'revokedBy'])
            ->where('tenant_id', $tenantId)
            ->orderByRaw("FIELD(status, 'assigned', 'used', 'revoked', 'expired')")
            ->orderByDesc('assigned_at')
            ->get();

        // Dédoublonnage : 1 entrée par user_id, code ASSIGNED prioritaire
        $byUser = [];
        foreach ($allCodes as $code) {
            $uid = $code->user_id ?? ('orphan_' . $code->id);
            if (
                !isset($byUser[$uid]) ||
                (
                    $code->status === VerificationCode::STATUS_ASSIGNED &&
                    $byUser[$uid]->status !== VerificationCode::STATUS_ASSIGNED
                )
            ) {
                $byUser[$uid] = $code;
            }
        }

        $rows = array_values($byUser);

        return view('admin.codes.index', compact('stats', 'rows'));
    }

    // ─── Stats AJAX ───────────────────────────────────────────────────────────

    public function stats(): JsonResponse
    {
        $stats = $this->service->dashboardStats($this->tenantId());

        return response()->json([
            'success' => true,
            'stats'   => $stats,
        ]);
    }

    // ─── Génération des codes manquants ───────────────────────────────────────

    public function generateMissing(Request $request): JsonResponse
    {
        try {
            $result = $this->service->generateMissing(
                $this->tenantId(),
                auth()->id()
            );

            $msg = "{$result['generated']} code(s) généré(s) et attribué(s) automatiquement.";

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'result'  => $result,
                    'message' => $msg,
                    'stats'   => $this->service->dashboardStats($this->tenantId()),
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

    // ─── Renouvellement trimestriel ───────────────────────────────────────────

    public function renewQuarter(Request $request): JsonResponse
    {
        try {
            $result = $this->service->renewQuarter(
                $this->tenantId(),
                auth()->id()
            );

            $msg = "Trimestre {$result['quarter']} renouvelé : {$result['expired']} expiré(s), {$result['generated']} généré(s).";

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'result'  => $result,
                    'message' => $msg,
                    'stats'   => $this->service->dashboardStats($this->tenantId()),
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

    // ─── Renouvellement forcé du trimestre courant ────────────────────────────

    public function forceRenew(): JsonResponse
    {
        try {
            $result = $this->service->forceRenewCurrentQuarter(
                $this->tenantId(),
                auth()->id()
            );

            $msg = "Renouvellement forcé {$result['quarter']} : "
                 . "{$result['revoked']} révoqué(s), {$result['generated']} généré(s).";

            return response()->json([
                'success' => true,
                'result'  => $result,
                'message' => $msg,
                'stats'   => $this->service->dashboardStats($this->tenantId()),
            ]);

        } catch (\DomainException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    // ─── Remplacement individuel ──────────────────────────────────────────────

    /**
     * Route model binding sur User — Laravel garantit l'existence de l'utilisateur.
     * On vérifie ensuite qu'il appartient bien au tenant de l'admin connecté.
     */
    public function replaceForUser(User $user): JsonResponse
    {
        // Sécurité IDOR : l'utilisateur doit appartenir au même tenant
        $this->assertBelongsToTenant((string) $user->tenant_id);

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

    // ─── Révocation individuelle ──────────────────────────────────────────────

    public function revoke(Request $request, VerificationCode $verificationCode): mixed
    {
        // Sécurité IDOR : le code doit appartenir au tenant de l'admin
        $this->assertBelongsToTenant((string) $verificationCode->tenant_id);

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
