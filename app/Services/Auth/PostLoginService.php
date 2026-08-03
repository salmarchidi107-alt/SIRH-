<?php

namespace App\Services\Auth;

use App\Models\Tenant;
use Illuminate\Support\Facades\Log;
use Stancl\Tenancy\Facades\Tenancy;



class PostLoginService
{


    /**
     * Initialize tenancy with Tenant model object.
     *
     * @param Tenant $tenantModel - Complete Tenant model instance (NOT ID/UUID)
     * @return void
     */
    public function initialize(Tenant $tenantModel): void
    {
        // Always end any existing tenancy first (safety)
        if (tenancy()->initialized) {
            tenancy()->end();
        }

        // Initialize with MODEL object (stancl/tenancy v3 compliant)
        tenancy()->initialize($tenantModel);

        // Expose tenant ID in config for queries
        config(['app.current_tenant_id' => $tenantModel->id]);

        Log::info('PostLoginService: Tenancy initialized', [
            'tenant_id' => $tenantModel->id,
            'tenant_name' => $tenantModel->name,
            'user_id' => auth()->id(),
        ]);
    }
}
