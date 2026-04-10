<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use App\Settings\GlobalSettings;
use Illuminate\Support\Facades\DB;

class CreateGlobalSettingsRow extends Command
{
    protected $signature = 'settings:create-global-row {--force}';
    protected $description = 'Create the initial GlobalSettings DB row';

    public function handle()
    {
        $force = $this->option('force');

        $exists = DB::table('settings')->where('group', 'global')->where('name', GlobalSettings::class)->exists();

        if ($exists && !$force) {
            $this->warn('GlobalSettings row already exists. Use --force to recreate.');
            return 1;
        }

        $payload = '{"mode_maintenance":false,"notifications_email":true,"email_support":"support@hospitalrh.ma","nom_plateforme":"HospitalRH"}';

        $id = Str::ulid();

        DB::table('settings')->updateOrInsert(
            [
                'group' => 'global',
                'name' => GlobalSettings::class,
            ],
            [
                'id' => $id,
                'payload' => $payload,
                'updated_at' => now(),
            ]
        );

        $this->info("GlobalSettings row created with ID: {$id}");
        $this->line('Payload: ' . $payload);

        return 0;
    }
}

