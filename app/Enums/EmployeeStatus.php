<?php

namespace App\Enums;

enum EmployeeStatus: string
{
    case Active = 'active';
    case Leave = 'leave';
    case Inactive = 'inactive';

    public function label(): string
    {
        return match($this) {
            self::Active => '● Actif',
            self::Leave => '◐ En congé',
            self::Inactive => '○ Inactif',
        };
    }
}

