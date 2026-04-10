<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class GlobalSettings extends Settings
{
    /**
     * Nom de la table dans la base de données (settings_properties).
     * Doit correspondre au group dans la migration.
     */
    public static function group(): string
    {
        return 'global';
    }

    // ── Propriétés ────────────────────────────────────────────────────────────

    public bool   $mode_maintenance;
    public bool   $notifications_email;
    public string $email_support;
    public string $nom_plateforme;
}
