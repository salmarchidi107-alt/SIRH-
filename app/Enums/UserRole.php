<?php

namespace App\Enums;

enum UserRole: string
{
    case Admin = 'admin';
    case Rh = 'rh';
    case Employee = 'employee';

    public function label(): string
    {
        return match($this) {
            self::Admin => 'Administrateur',
            self::Rh => 'Responsable RH',
            self::Employee => 'Employé',
        };
    }
}

