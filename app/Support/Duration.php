<?php

namespace App\Support;

class Duration
{
    /**
     * Convertit une saisie libre ("1h30", "1h", "45m", "1:30", "90") en minutes.
     * Retourne null si la saisie est invalide.
     */
    public static function toMinutes(?string $input): ?int
    {
        if (! $input) {
            return null;
        }

        $input = trim(strtolower($input));

        // Format "1h30", "1h", "1h05"
        if (preg_match('/^(\d+)\s*h\s*(\d{1,2})?$/', $input, $m)) {
            $hours = (int) $m[1];
            $minutes = isset($m[2]) ? (int) $m[2] : 0;
            return $hours * 60 + $minutes;
        }

        // Format "45m" ou "45min"
        if (preg_match('/^(\d+)\s*m(in)?$/', $input, $m)) {
            return (int) $m[1];
        }

        // Format "1:30"
        if (preg_match('/^(\d+):(\d{2})$/', $input, $m)) {
            return ((int) $m[1]) * 60 + (int) $m[2];
        }

        // Nombre entier brut = minutes
        if (preg_match('/^\d+$/', $input)) {
            return (int) $input;
        }

        return null;
    }

    /** Formate un nombre de minutes en "1h30" (ou "45m" si < 1h). */
    public static function toHuman(int $minutes): string
    {
        $h = intdiv($minutes, 60);
        $m = $minutes % 60;

        if ($h === 0) {
            return "{$m}m";
        }

        return $m === 0 ? "{$h}h" : sprintf('%dh%02d', $h, $m);
    }
}
