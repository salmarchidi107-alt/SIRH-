<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;
use App\Models\Tenant;

return new class extends Migration
{
    public function up(): void
    {
        // First, delete duplicate plannings (keep lowest ID)
        $deleted = DB::delete("DELETE t1 FROM plannings t1
            INNER JOIN plannings t2 
            WHERE 
                t1.id > t2.id AND 
                t1.employee_id = t2.employee_id AND 
                t1.date = t2.date AND 
                t1.tenant_id <=> t2.tenant_id");

        echo "Deleted $deleted duplicate plannings\n";
        
        // Get current tenant
        $currentTenantId = Config::get('app.current_tenant_id');
        if (empty($currentTenantId)) {
            $currentTenantId = Tenant::first()?->id ?? null;
        }
        
        if (!$currentTenantId) {
            echo "No tenant found. Skipping tenant update.\n";
            return;
        }
        
        echo "Using tenant_id = $currentTenantId\n";
        
        // Update only NULL tenant_id to avoid unique constraint
        $updated = DB::table('plannings')
            ->whereNull('tenant_id')
            ->update(['tenant_id' => $currentTenantId]);
            
        echo "Updated $updated plannings (NULL -> $currentTenantId)\n";
        
        // Final count
        $scopedCount = DB::table('plannings')->where('tenant_id', $currentTenantId)->count();
        $totalCount = DB::table('plannings')->count();
        echo "Scoped ($currentTenantId): $scopedCount / Total: $totalCount\n";
    }

    public function down(): void
    {
        echo "Data fix migration - no down action\n";
    }
};

