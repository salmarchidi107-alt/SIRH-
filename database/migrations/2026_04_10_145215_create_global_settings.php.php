<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

/**
 * Migration Spatie Settings — groupe "global"
 *
 * Commande pour générer ce fichier :
 *   php artisan make:settings-migration CreateGlobalSettings
 *
 * Commande pour exécuter :
 *   php artisan migrate
 */
class CreateGlobalSettings extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('global.mode_maintenance',    false);
        $this->migrator->add('global.notifications_email', true);
        $this->migrator->add('global.email_support',       'support@hospitalrh.ma');
        $this->migrator->add('global.nom_plateforme',      'HospitalRH');
    }

    public function down(): void
    {
        $this->migrator->delete('global.mode_maintenance');
        $this->migrator->delete('global.notifications_email');
        $this->migrator->delete('global.email_support');
        $this->migrator->delete('global.nom_plateforme');
    }
}
