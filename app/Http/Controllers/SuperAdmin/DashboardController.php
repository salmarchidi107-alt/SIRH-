<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Enums\TenantStatus;
use App\Http\Controllers\Controller;
use App\Models\Tenant as Tenant;
use App\Models\User;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'total_tenants'       => Tenant::count(),
            'total_users'       => User::whereNotNull('tenant_id')->count(),
            'active_tenants'   => Tenant::where('status', TenantStatus::Active->value)->count(),
            'trial_tenants'    => Tenant::where('status', TenantStatus::Trial->value)->count(),
            'inactive_tenants' => Tenant::where('status', 'inactive')->count(),
            'new_tenants_month'   => Tenant::whereMonth('created_at', Carbon::now()->month)
                                        ->whereYear('created_at', Carbon::now()->year)
                                        ->count(),
            'tenants_this_year'  => Tenant::whereYear('created_at', Carbon::now()->year)->count(),
        ];

        $recent = Tenant::latest()->take(5)->get();

        return view('superadmin.dashboard', compact('stats', 'recent'));
    }
}
