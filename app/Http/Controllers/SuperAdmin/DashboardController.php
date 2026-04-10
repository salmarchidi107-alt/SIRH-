<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Enums\TenantStatus;
use App\Http\Controllers\Controller;
use App\Models\Tenant as Tenant;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'total_tenants'  => Tenant::count(),
            'active_tenants' => Tenant::where('status', TenantStatus::Active->value)->count(),
            'trial_tenants'  => Tenant::where('status', TenantStatus::Trial->value)->count(),
            'inactive_tenants' => Tenant::where('status', 'inactive')->count(),
        ];

        $recent = Tenant::latest()->take(5)->get();

        return view('superadmin.dashboard', compact('stats', 'recent'));
    }
}
