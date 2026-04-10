<?php

namespace App\Enums;

enum TenantStatus: string
{
    case Active    = 'active';
    case Suspended = 'suspended';
    case Trial     = 'trial';
    case Inactive  = 'inactive';

    public function label(): string
    {
        return match($this) {
            self::Active    => 'Actif',
            self::Suspended => 'Suspendu',
            self::Trial     => 'Essai',
            self::Inactive  => 'Inactif',
        };
    }

    public function badgeClass(): string
    {
        return match($this) {
            self::Active    => 'active',
            self::Suspended => 'suspended',
            self::Trial     => 'trial',
            self::Inactive  => 'inactive',
        };
    }

    public function dotClass(): string
    {
        return match($this) {
            self::Active    => 'green',
            self::Suspended => 'yellow',
            self::Trial     => 'blue',
            self::Inactive  => 'gray',
        };
    }
}
