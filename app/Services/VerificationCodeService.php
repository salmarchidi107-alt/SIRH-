<?php

namespace App\Services;

use App\Models\{VerificationCode, Tenant, User};
use Illuminate\Support\Facades\DB;

class VerificationCodeService
{
    // ─── Utilisateurs actifs d'un tenant ─────────────────────────────────────

    private function activeUsersForTenant(string $tenantId)
    {
        return User::where('tenant_id', $tenantId)
            ->whereIn('role', ['admin', 'employee', 'rh'])
            ->orderBy('name')
            ->get();
    }

    // ─── Stats dashboard par tenant ──────────────────────────────────────────

    public function dashboardStats(string $tenantId): array
    {
        $activeUsers = $this->activeUsersForTenant($tenantId);
        $activeCount = $activeUsers->count();
        $activeIds   = $activeUsers->pluck('id');

        // Codes actifs (ASSIGNED) pour les utilisateurs actifs
        $assignedCount = VerificationCode::forTenant($tenantId)
            ->assigned()
            ->whereIn('user_id', $activeIds)
            ->count();

        // Codes utilisés au moins une fois (ASSIGNED + used_at non null)
        $usedAtLeastOnce = VerificationCode::forTenant($tenantId)
            ->usedAtLeastOnce()
            ->whereIn('user_id', $activeIds)
            ->count();

        // Codes jamais utilisés (ASSIGNED + used_at null)
        $neverUsed = VerificationCode::forTenant($tenantId)
            ->neverUsed()
            ->whereIn('user_id', $activeIds)
            ->count();

        // ── Révocations MANUELLES uniquement ─────────────────────────────────
        // On exclut les révocations générées par replaceForUser() (raison 'Remplacement manuel')
        $revokedManual = VerificationCode::forTenant($tenantId)
            ->where('status', VerificationCode::STATUS_REVOKED)
            ->where(function ($q) {
                $q->whereNull('revoke_reason')
                  ->orWhere('revoke_reason', '')
                  ->orWhere('revoke_reason', 'NOT LIKE', '%Remplacement%');
            })
            ->count();

        $totals = VerificationCode::forTenant($tenantId)
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status')
            ->toArray();

        $missingCount = max(0, $activeCount - $assignedCount);

        return [
            'active_employees'   => $activeCount,
            'assigned_count'     => $assignedCount,
            'used_at_least_once' => $usedAtLeastOnce,
            'never_used'         => $neverUsed,
            'missing_count'      => $missingCount,
            'revoked_count'      => $revokedManual,   // révocations manuelles uniquement
            'used_total'         => $totals[VerificationCode::STATUS_USED]    ?? 0,
            'revoked_total'      => $totals[VerificationCode::STATUS_REVOKED] ?? 0,
            'coverage_pct'       => $activeCount > 0
                ? (int) round(($assignedCount / $activeCount) * 100)
                : 0,
        ];
    }

    // ─── Générer les codes manquants ─────────────────────────────────────────

    public function generateMissing(string $tenantId, int $generatedBy): array
    {
        return DB::transaction(function () use ($tenantId, $generatedBy) {
            $activeUsers = $this->activeUsersForTenant($tenantId);

            if ($activeUsers->isEmpty()) {
                throw new \DomainException('Ce tenant n\'a aucun utilisateur actif.');
            }

            $coveredIds = VerificationCode::forTenant($tenantId)
                ->assigned()
                ->whereIn('user_id', $activeUsers->pluck('id'))
                ->pluck('user_id')
                ->toArray();

            $usersNeedingCode = $activeUsers->whereNotIn('id', $coveredIds)->values();

            if ($usersNeedingCode->isEmpty()) {
                throw new \DomainException(
                    'Tous les utilisateurs actifs ont déjà un code actif.'
                );
            }

            $created = [];

            foreach ($usersNeedingCode as $user) {
                $alreadyHasCode = VerificationCode::where('user_id', $user->id)
                    ->where('status', VerificationCode::STATUS_ASSIGNED)
                    ->exists();

                if ($alreadyHasCode) {
                    continue;
                }

                $code = VerificationCode::create([
                    'code'         => VerificationCode::generateUniqueCode($tenantId),
                    'tenant_id'    => $tenantId,
                    'status'       => VerificationCode::STATUS_ASSIGNED,
                    'user_id'      => $user->id,
                    'assigned_by'  => $generatedBy,
                    'assigned_at'  => now(),
                    'generated_by' => $generatedBy,
                ]);

                $created[] = [
                    'user_id'   => $user->id,
                    'user_name' => $user->name,
                    'code'      => $code->code,
                    'code_id'   => $code->id,
                ];
            }

            return [
                'generated' => count($created),
                'employees' => $created,
            ];
        });
    }

    // ─── Attribution automatique à un nouvel utilisateur ─────────────────────

    public function assignToNewUser(User $user): VerificationCode
    {
        return DB::transaction(function () use ($user) {
            $existing = VerificationCode::where('user_id', $user->id)
                ->where('status', VerificationCode::STATUS_ASSIGNED)
                ->first();

            if ($existing) {
                return $existing;
            }

            return VerificationCode::create([
                'code'         => VerificationCode::generateUniqueCode($user->tenant_id),
                'tenant_id'    => $user->tenant_id,
                'status'       => VerificationCode::STATUS_ASSIGNED,
                'user_id'      => $user->id,
                'assigned_by'  => null,
                'assigned_at'  => now(),
                'generated_by' => null,
            ]);
        });
    }

    /** @deprecated Utiliser assignToNewUser() à la place. */
    public function assignToNewEmployee(User $user): VerificationCode
    {
        return $this->assignToNewUser($user);
    }

    // ─── Révocation individuelle ─────────────────────────────────────────────

    public function revoke(VerificationCode $code, int $revokedBy, string $reason = ''): void
    {
        $code->revoke($revokedBy, $reason);
    }

    // ─── Remplacement individuel ─────────────────────────────────────────────

    public function replaceForUser(User $user, int $replacedBy): VerificationCode
    {
        if (!$user->exists) {
            throw new \DomainException(
                "Impossible de remplacer le code d'un utilisateur non persisté (ID manquant). " .
                "Utilisez User::findOrFail(\$id) avant d'appeler replaceForUser()."
            );
        }

        return DB::transaction(function () use ($user, $replacedBy) {
            VerificationCode::where('user_id', $user->id)
                ->where('status', VerificationCode::STATUS_ASSIGNED)
                ->each(fn ($c) => $c->revoke($replacedBy, 'Remplacement manuel'));

            return VerificationCode::create([
                'code'         => VerificationCode::generateUniqueCode($user->tenant_id),
                'tenant_id'    => $user->tenant_id,
                'status'       => VerificationCode::STATUS_ASSIGNED,
                'user_id'      => $user->id,
                'assigned_by'  => $replacedBy,
                'assigned_at'  => now(),
                'generated_by' => $replacedBy,
            ]);
        });
    }
}
