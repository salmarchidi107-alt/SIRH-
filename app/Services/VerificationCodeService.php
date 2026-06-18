<?php

namespace App\Services;

use App\Models\{VerificationCode, Tenant, User};
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class VerificationCodeService
{
    // ─── Trimestre courant ───────────────────────────────────────────────────

    public function currentQuarter(): string
    {
        $q = (int) ceil(now()->month / 3);
        return 'T' . $q . '-' . now()->year;
    }

    public function previousQuarter(): string
    {
        $now = now();
        $q   = (int) ceil($now->month / 3);

        if ($q === 1) {
            return 'T4-' . ($now->year - 1);
        }

        return 'T' . ($q - 1) . '-' . $now->year;
    }

    public function nextRenewalDate(): Carbon
    {
        $q      = (int) ceil(now()->month / 3);
        $starts = [1 => [4, 1], 2 => [7, 1], 3 => [10, 1], 4 => [1, 1]];
        [$month, $day] = $starts[$q];
        $year = $q === 4 ? now()->year + 1 : now()->year;

        return Carbon::create($year, $month, $day);
    }

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
        $quarter     = $this->currentQuarter();
        $activeUsers = $this->activeUsersForTenant($tenantId);
        $activeCount = $activeUsers->count();
        $activeIds   = $activeUsers->pluck('id');

        // Codes attribués ce trimestre pour les utilisateurs actifs
        $assignedThisQuarter = VerificationCode::forTenant($tenantId)
            ->forQuarter($quarter)
            ->assigned()
            ->whereIn('user_id', $activeIds)
            ->count();

        // Codes utilisés au moins une fois ce trimestre (ASSIGNED + used_at non null)
        $usedAtLeastOnce = VerificationCode::forTenant($tenantId)
            ->forQuarter($quarter)
            ->usedAtLeastOnce()
            ->whereIn('user_id', $activeIds)
            ->count();

        // Codes jamais utilisés ce trimestre (ASSIGNED + used_at null)
        $neverUsed = VerificationCode::forTenant($tenantId)
            ->forQuarter($quarter)
            ->neverUsed()
            ->whereIn('user_id', $activeIds)
            ->count();

        // ── Révocations MANUELLES uniquement ce trimestre ─────────────────────
        // On exclut les révocations automatiques :
        //   - 'Remplacement manuel'  → générées par replaceForUser()
        //   - 'Renouvellement forcé' → générées par forceRenewCurrentQuarter()
        // Seules les révocations explicites via le bouton "Révoquer" sont comptées.
        $revokedThisQuarter = VerificationCode::forTenant($tenantId)
            ->forQuarter($quarter)
            ->where('status', VerificationCode::STATUS_REVOKED)
            ->where(function ($q) {
                $q->whereNull('revoke_reason')
                  ->orWhere('revoke_reason', '')
                  ->orWhere(function ($q2) {
                      $q2->where('revoke_reason', 'NOT LIKE', '%Remplacement%')
                         ->where('revoke_reason', 'NOT LIKE', '%forcé%')
                         ->where('revoke_reason', 'NOT LIKE', '%force%');
                  });
            })
            ->count();

        // Totaux historiques (tous trimestres) — conservés pour compatibilité SuperAdmin
        $totals = VerificationCode::forTenant($tenantId)
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status')
            ->toArray();

        $missingCount = max(0, $activeCount - $assignedThisQuarter);

        return [
            'active_employees'      => $activeCount,
            'assigned_this_quarter' => $assignedThisQuarter,
            'used_at_least_once'    => $usedAtLeastOnce,
            'never_used'            => $neverUsed,
            'missing_count'         => $missingCount,
            'revoked_this_quarter'  => $revokedThisQuarter,  // ← révocations manuelles uniquement
            'used_total'            => $totals[VerificationCode::STATUS_USED]    ?? 0,
            'revoked_total'         => $totals[VerificationCode::STATUS_REVOKED] ?? 0,
            'expired_total'         => $totals[VerificationCode::STATUS_EXPIRED] ?? 0,
            'current_quarter'       => $quarter,
            'next_renewal'          => $this->nextRenewalDate()->format('d/m/Y'),
            'coverage_pct'          => $activeCount > 0
                ? (int) round(($assignedThisQuarter / $activeCount) * 100)
                : 0,
        ];
    }

    // ─── Générer les codes manquants ─────────────────────────────────────────

    public function generateMissing(string $tenantId, int $generatedBy): array
    {
        $quarter = $this->currentQuarter();

        return DB::transaction(function () use ($tenantId, $quarter, $generatedBy) {
            $activeUsers = $this->activeUsersForTenant($tenantId);

            if ($activeUsers->isEmpty()) {
                throw new \DomainException('Ce tenant n\'a aucun utilisateur actif.');
            }

            $coveredIds = VerificationCode::forTenant($tenantId)
                ->forQuarter($quarter)
                ->assigned()
                ->whereIn('user_id', $activeUsers->pluck('id'))
                ->pluck('user_id')
                ->toArray();

            $usersNeedingCode = $activeUsers->whereNotIn('id', $coveredIds)->values();

            if ($usersNeedingCode->isEmpty()) {
                throw new \DomainException(
                    'Tous les utilisateurs actifs ont déjà un code pour le trimestre ' . $quarter . '.'
                );
            }

            $created = [];

            foreach ($usersNeedingCode as $user) {
                $alreadyHasCode = VerificationCode::where('user_id', $user->id)
                    ->where('quarter', $quarter)
                    ->where('status', VerificationCode::STATUS_ASSIGNED)
                    ->exists();

                if ($alreadyHasCode) {
                    continue;
                }

                $code = VerificationCode::create([
                    'code'         => VerificationCode::generateUniqueCode(),
                    'tenant_id'    => $tenantId,
                    'quarter'      => $quarter,
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
        $quarter = $this->currentQuarter();

        return DB::transaction(function () use ($user, $quarter) {
            $existing = VerificationCode::where('user_id', $user->id)
                ->where('quarter', $quarter)
                ->where('status', VerificationCode::STATUS_ASSIGNED)
                ->first();

            if ($existing) {
                return $existing;
            }

            return VerificationCode::create([
                'code'         => VerificationCode::generateUniqueCode(),
                'tenant_id'    => $user->tenant_id,
                'quarter'      => $quarter,
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

    // ─── Renouvellement trimestriel ──────────────────────────────────────────

    public function renewQuarter(string $tenantId, int $triggeredBy): array
    {
        $currentQuarter  = $this->currentQuarter();
        $previousQuarter = $this->previousQuarter();

        return DB::transaction(function () use ($tenantId, $currentQuarter, $previousQuarter, $triggeredBy) {
            $toExpire = VerificationCode::forTenant($tenantId)
                ->forQuarter($previousQuarter)
                ->active()
                ->get();

            $expiredCount = 0;
            foreach ($toExpire as $code) {
                $code->expire();
                $expiredCount++;
            }

            $activeUsers = $this->activeUsersForTenant($tenantId);

            $alreadyCoveredIds = VerificationCode::forTenant($tenantId)
                ->forQuarter($currentQuarter)
                ->assigned()
                ->whereIn('user_id', $activeUsers->pluck('id'))
                ->pluck('user_id')
                ->toArray();

            $generatedCount = 0;
            foreach ($activeUsers as $user) {
                if (in_array($user->id, $alreadyCoveredIds)) {
                    continue;
                }

                VerificationCode::create([
                    'code'         => VerificationCode::generateUniqueCode(),
                    'tenant_id'    => $tenantId,
                    'quarter'      => $currentQuarter,
                    'status'       => VerificationCode::STATUS_ASSIGNED,
                    'user_id'      => $user->id,
                    'assigned_by'  => $triggeredBy,
                    'assigned_at'  => now(),
                    'generated_by' => $triggeredBy,
                ]);
                $generatedCount++;
            }

            return [
                'expired'   => $expiredCount,
                'generated' => $generatedCount,
                'quarter'   => $currentQuarter,
            ];
        });
    }

    // ─── Renouvellement forcé du trimestre courant ────────────────────────────

    public function forceRenewCurrentQuarter(string $tenantId, int $triggeredBy): array
    {
        $quarter = $this->currentQuarter();

        return DB::transaction(function () use ($tenantId, $quarter, $triggeredBy) {
            $toRevoke = VerificationCode::forTenant($tenantId)
                ->forQuarter($quarter)
                ->active()
                ->get();

            $revokedCount = 0;
            foreach ($toRevoke as $code) {
                $code->revoke($triggeredBy, 'Renouvellement forcé');
                $revokedCount++;
            }

            $activeUsers    = $this->activeUsersForTenant($tenantId);
            $generatedCount = 0;

            foreach ($activeUsers as $user) {
                VerificationCode::create([
                    'code'         => VerificationCode::generateUniqueCode(),
                    'tenant_id'    => $tenantId,
                    'quarter'      => $quarter,
                    'status'       => VerificationCode::STATUS_ASSIGNED,
                    'user_id'      => $user->id,
                    'assigned_by'  => $triggeredBy,
                    'assigned_at'  => now(),
                    'generated_by' => $triggeredBy,
                ]);
                $generatedCount++;
            }

            return [
                'revoked'   => $revokedCount,
                'generated' => $generatedCount,
                'quarter'   => $quarter,
            ];
        });
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

        $quarter = $this->currentQuarter();

        return DB::transaction(function () use ($user, $quarter, $replacedBy) {
            VerificationCode::where('user_id', $user->id)
                ->where('quarter', $quarter)
                ->where('status', VerificationCode::STATUS_ASSIGNED)
                ->each(fn ($c) => $c->revoke($replacedBy, 'Remplacement manuel'));

            return VerificationCode::create([
                'code'         => VerificationCode::generateUniqueCode(),
                'tenant_id'    => $user->tenant_id,
                'quarter'      => $quarter,
                'status'       => VerificationCode::STATUS_ASSIGNED,
                'user_id'      => $user->id,
                'assigned_by'  => $replacedBy,
                'assigned_at'  => now(),
                'generated_by' => $replacedBy,
            ]);
        });
    }
}
