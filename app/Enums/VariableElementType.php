<?php

namespace App\Enums;

enum VariableElementType: string
{
    case GAIN = 'gain';
    case RETENUE = 'retenue';

    public function label(): string
    {
        return match($this) {
            self::GAIN => 'Gain',
            self::RETENUE => 'Retenue',
        };
    }

    public static function labels(): array
    {
        return collect(self::cases())->mapWithKeys(fn ($case) => [$case->value => $case->label()])->toArray();
    }
}

