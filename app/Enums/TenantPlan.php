<?php

namespace App\Enums;

enum TenantPlan: string
{
    case Starter  = 'starter';
    case Pro      = 'pro';
    case Enterprise = 'enterprise';

    public function label(): string
    {
        return match($this) {
            self::Starter    => 'Starter',
            self::Pro        => 'Pro',
            self::Enterprise => 'Enterprise',
        };
    }

    public function badgeClass(): string
    {
        return match($this) {
            self::Starter    => 'plan-starter',
            self::Pro        => 'plan-pro',
            self::Enterprise => 'plan-ent',
        };
    }
}
