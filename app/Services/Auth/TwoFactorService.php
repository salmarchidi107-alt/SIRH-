<?php

namespace App\Services\Auth;

use App\Models\Tenant;
use App\Models\User;
use App\Models\VerificationCode;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;


class TwoFactorService
{
    private const MAX_ATTEMPTS     = 5;
    private const LOCKOUT_SECONDS  = 600;

    public function __construct(
        private PostLoginService $postLoginService,
    ) {}

    public function isAlreadyVerified(int $userId): bool
    {
        return session('2fa_verified') && session('2fa_user_id') === $userId;
    }

    private function throttleKey(int $userId, string $ip): string
    {
        return '2fa_otp|' . $userId . '|' . $ip;
    }

    public function getLockoutSecondsRemaining(int $userId, string $ip): ?int
    {
        $key = $this->throttleKey($userId, $ip);

        if (! RateLimiter::tooManyAttempts($key, self::MAX_ATTEMPTS)) {
            return null;
        }

        return RateLimiter::availableIn($key);
    }

    public function lockoutLogout(): void
    {
        if (tenancy()->initialized) {
            tenancy()->end();
        }

        Auth::logout();
    }

    public function verifyCode(int $userId, string $ip, string $code): array
    {
        $key = $this->throttleKey($userId, $ip);

        $record = VerificationCode::where('code', $code)
            ->where('user_id', $userId)
            ->first();

        if (! $record) {
            RateLimiter::hit($key, self::LOCKOUT_SECONDS);
            $remaining = self::MAX_ATTEMPTS - RateLimiter::attempts($key);

            return ['result' => 'invalid', 'remaining' => $remaining];
        }

        if ($record->status !== VerificationCode::STATUS_ASSIGNED) {
            RateLimiter::hit($key, self::LOCKOUT_SECONDS);

            return ['result' => 'revoked'];
        }

        // Code valide — statut non modifié, réutilisable indéfiniment
        RateLimiter::clear($key);
        VerificationCode::consume($code, $userId);

        session([
            '2fa_verified' => true,
            '2fa_user_id'  => $userId,
        ]);

        $tenancyError = $this->initializeTenancyForUser($userId);
        if ($tenancyError) {
            return $tenancyError;
        }

        return ['result' => 'success'];
    }

    private function initializeTenancyForUser(int $userId): ?array
    {
        $user = User::find($userId);

        if (! $user || $user->isSuperAdmin()) {
            return null;
        }

        // Vérifier que l'user a bien un tenant assigné
        if (! $user->tenant_id) {
            Auth::logout();
            Log::warning('Login sans tenant_id', [
                'email'     => $user->email,
                'role'      => $user->role,
                'tenant_id' => null,
            ]);

            return ['result' => 'no_tenant'];
        }

        // Résoudre le tenant
        $tenant = Tenant::find($user->tenant_id);

        if (! $tenant) {
            Auth::logout();
            Log::error('Tenant introuvable lors du login', [
                'email'     => $user->email,
                'tenant_id' => $user->tenant_id,
            ]);

            return ['result' => 'tenant_not_found'];
        }

        // Initialiser la tenancy
        $this->postLoginService->initialize($tenant);

        return null;
    }
}
