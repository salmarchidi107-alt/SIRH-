<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\User;
use App\Enums\TenantPlan;
use App\Enums\TenantStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;

class TenantController extends Controller
{
    public function index(Request $request)
    {
        $query = Tenant::with('admin')->withCount('users');

        if ($s = $request->search) {
            $query->where(fn($q) => $q
                ->where('name',    'like', "%{$s}%")
                ->orWhere('slug',   'like', "%{$s}%")
                ->orWhere('sector', 'like', "%{$s}%")
            );
        }

        $tenants = $query->orderByDesc('created_at')->paginate(20)->withQueryString();

        $recentTenants = Tenant::latest()->take(5)->pluck('name');

        $counts = [
            'all'            => Tenant::count(),
            'active'         => Tenant::active()->count(),
            'trial'          => Tenant::byStatus('trial')->count(),
            'suspended'      => Tenant::byStatus('suspended')->count(),
            'inactive'       => Tenant::byStatus('inactive')->count(),
            'total_users'    => User::whereNotNull('tenant_id')->count(),
            'new_this_month' => Tenant::where('created_at', '>=', now()->startOfMonth())->count(),
        ];

        return view('superadmin.tenants.index', compact('tenants', 'counts', 'recentTenants'));
    }

    public function create()
    {
        return view('superadmin.tenants.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'company_name'  => 'required|string|max:100',
            'slug'          => 'required|string|max:50|unique:tenants,slug|regex:/^[a-z0-9\-]+$/',
            'sector'        => 'nullable|string|max:50',
            'region'        => 'required|string',
            'address'       => 'required|string|max:255',
            'phone'         => 'required|string|max:20',
            'ice'           => 'required|string|size:15',
            'email_societe' => 'required|email|max:100',
            'website'       => 'nullable|url|max:100',
            'logo'          => 'nullable|image|mimes:png,svg,jpg,jpeg|max:2048',
            'brand_color'   => 'required|regex:/^#[0-9a-fA-F]{6}$/',
            'sidebar_color' => 'required|regex:/^#[0-9a-fA-F]{6}$/',
            'first_name'    => 'required|string|max:50',
            'last_name'     => 'required|string|max:50',
            'admin_email'   => 'required|email|unique:users,email',
            'temp_password' => 'required|string|min:8',
        ]);

        DB::transaction(function () use ($data, $request) {
            $logoPath = $request->hasFile('logo')
                ? $request->file('logo')->store('tenants/logos', 'public')
                : null;

            $tenant = Tenant::create([
                'id'            => Str::uuid()->toString(),
                'name'          => $data['company_name'],
                'slug'          => $data['slug'],
                'sector'        => $data['sector'] ?? null,
                'region'        => $data['region'],
                'address'       => $data['address'],
                'phone'         => $data['phone'],
                'ice'           => $data['ice'],
                'email_societe' => $data['email_societe'],
                'website'       => $data['website'] ?? null,
                'logo_path'     => $logoPath,
                'brand_color'   => $data['brand_color'],
                'sidebar_color' => $data['sidebar_color'],
                'database_name' => 'tenant_' . str_replace('-', '_', $data['slug']),
            ]);

            $domainName = $data['slug'] . '.hospitalrh.test';

            if (DB::table('domains')->where('domain', $domainName)->exists()) {
                throw ValidationException::withMessages([
                    'slug' => "Le domaine {$domainName} existe déjà.",
                ]);
            }

            $tenant->domains()->create([
                'id'     => Str::uuid()->toString(),
                'domain' => $domainName,
            ]);

            User::create([
                'name'      => $data['first_name'] . ' ' . $data['last_name'],
                'email'     => $data['admin_email'],
                'password'  => Hash::make($data['temp_password']),
                'role'      => 'admin',
                'tenant_id' => $tenant->id,
            ]);
        });

        return redirect()->route('superadmin.tenants.index')
            ->with('success', 'Tenant créé avec succès.');
    }

    public function edit(Tenant $tenant)
    {
        $plans    = TenantPlan::cases();
        $statuses = TenantStatus::cases();

        return view('superadmin.tenants.edit', compact('tenant', 'plans', 'statuses'));
    }

    public function update(Request $request, Tenant $tenant)
    {
        $data = $request->validate([
            'company_name'  => 'required|string|max:100',
            'slug'          => ['required', 'regex:/^[a-z0-9\-]+$/',
                                Rule::unique('tenants', 'slug')->ignore($tenant->id)],
            'sector'        => 'nullable|string|max:50',
            'region'        => 'required|string',
            'address'       => 'required|string|max:255',
            'phone'         => 'required|string|max:20',
            'ice'           => 'required|string|size:15',
            'email_societe' => 'required|email|max:100',
            'website'       => 'nullable|url|max:100',
            'logo'          => 'nullable|image|mimes:png,svg,jpg,jpeg|max:2048',
            'brand_color'   => 'required|regex:/^#[0-9a-fA-F]{6}$/',
            'sidebar_color' => 'required|regex:/^#[0-9a-fA-F]{6}$/',
        ]);

        if ($request->hasFile('logo')) {
            if ($tenant->logo_path) Storage::disk('public')->delete($tenant->logo_path);
            $logoPath = $request->file('logo')->store('tenants/logos', 'public');
        } else {
            $logoPath = $tenant->logo_path;
        }

        $tenant->update([
            'name'          => $data['company_name'],
            'slug'          => $data['slug'],
            'sector'        => $data['sector'] ?? null,
            'region'        => $data['region'],
            'address'       => $data['address'],
            'phone'         => $data['phone'],
            'ice'           => $data['ice'],
            'email_societe' => $data['email_societe'],
            'website'       => $data['website'] ?? null,
            'logo_path'     => $logoPath,
            'brand_color'   => $data['brand_color'],
            'sidebar_color' => $data['sidebar_color'],
        ]);

        return back()->with('success', 'Tenant mis à jour.');
    }

    public function destroy(Tenant $tenant)
    {
        if ($tenant->logo_path) Storage::disk('public')->delete($tenant->logo_path);
        User::where('tenant_id', $tenant->id)->delete();
        $tenant->delete();

        return redirect()->route('superadmin.tenants.index')
            ->with('success', 'Tenant supprimé.');
    }

    public function show(Tenant $tenant)
    {
        $tenant->load('admin')->loadCount('users');
        return view('superadmin.tenants.show', compact('tenant'));
    }
}