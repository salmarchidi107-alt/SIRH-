<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Tenant;
use Illuminate\Support\Facades\Log;

class CheckUsersTenants extends Command
{
    protected $signature = 'users:check-tenants';
    protected $description = 'Check users with invalid/missing tenants';

    public function handle()
    {
        $this->info("=== Users-Tenants Check ===");

        $users = User::with('tenant')->get();

        $invalid = [];
        $superadmins = [];
        $valid = [];

        foreach ($users as $user) {
            if ($user->isSuperAdmin()) {
                $superadmins[] = $user->email . " (superadmin, tenant_id=" . ($user->tenant_id ?? 'NULL') . ")";
            } elseif (empty($user->tenant_id)) {
                $invalid[] = $user->email . " (role={$user->role}, tenant_id=NULL)";
            } elseif (!$user->tenant) {
                $invalid[] = $user->email . " (role={$user->role}, tenant_id={$user->tenant_id} MISSING)";
            } else {
                $valid[] = $user->email . " (role={$user->role}, tenant={$user->tenant->name})";
            }
        }

        $this->table(
            ['Type', 'Count'],
            [
                ['Superadmins', count($superadmins)],
                ['Valid tenant users', count($valid)],
                ['Invalid/missing tenants', count($invalid)],
            ]
        );

        if (!empty($invalid)) {
            $this->warn("\nInvalid users:");
            foreach ($invalid as $item) {
                $this->line("  " . $item);
            }
            $this->warn("\nFix: php artisan tinker");
            $this->warn("User::whereIn('email', [...])->update(['tenant_id' => null]); // or link to tenant");
        }

        if (!empty($superadmins)) {
            $this->info("\nSuperadmins OK:");
            foreach (array_slice($superadmins, 0, 5) as $sa) {
                $this->line("  " . $sa);
            }
        }

        $tenantCount = Tenant::count();
        $this->info("\nTotal tenants in DB: {$tenantCount}");
        if ($tenantCount === 0) {
            $this->warn("NO TENANTS! Create via superadmin UI or tinker.");
        }

        Log::info('users:check-tenants', [
            'superadmins' => count($superadmins),
            'valid' => count($valid),
            'invalid' => count($invalid),
            'tenants' => $tenantCount,
        ]);

        return self::SUCCESS;
    }
}
?>

