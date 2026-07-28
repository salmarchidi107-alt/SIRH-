<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class TenantController extends Controller
{
    public function index(Request $request)
    {
        $query = Tenant::with('admin')->withCount('users');

        if ($s = $request->search) {
            $query->where(fn($q) => $q
                ->where('name',   'like', "%{$s}%")
                ->orWhere('sector', 'like', "%{$s}%")
            );
        }

        $tenants       = $query->orderByDesc('created_at')->paginate(20)->withQueryString();
        $recentTenants = Tenant::latest()->take(5)->pluck('name');

        $counts = [
            'all'            => Tenant::count(),
            'total_users' => User::whereNotNull('tenant_id')
                     ->where('role', 'employee')
                     ->count(),
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
            'sector'        => 'nullable|string|max:50',
            'region'        => 'required|string',
            'region_other'  => 'nullable|string|max:100',
            'address'       => 'required|string|max:255',
            'phone'         => 'required|string|max:20',
            'ice'           => 'required|string|size:15',
            'email_societe' => 'required|email|max:100',
            'website'       => 'nullable|url|max:100',
            'logo'          => 'nullable|image|mimes:png,svg,jpg,jpeg|max:2048',
            'brand_color'   => 'nullable|regex:/^#[0-9a-fA-F]{6}$/',
            'sidebar_color' => 'nullable|regex:/^#[0-9a-fA-F]{6}$/',
            'timezone'      => 'required|timezone',
            'first_name'    => 'required|string|max:50',
            'last_name'     => 'required|string|max:50',
            'admin_email'   => 'required|email|unique:users,email',
            'temp_password' => 'required|string|min:8',
        ]);

        $region = ($data['region'] === 'Autre' && !empty($data['region_other']))
            ? $data['region_other']
            : $data['region'];

        DB::transaction(function () use ($data, $request, $region) {
            $logoPath = $request->hasFile('logo')
                ? $request->file('logo')->store('tenants/logos', 'public')
                : null;

            $tenant = Tenant::create([
                'id'            => Str::uuid()->toString(),
                'name'          => $data['company_name'],
                'sector'        => $data['sector'] ?? null,
                'region'        => $region,
                'address'       => $data['address'],
                'phone'         => $data['phone'],
                'ice'           => $data['ice'],
                'email_societe' => $data['email_societe'],
                'website'       => $data['website'] ?? null,
                'logo_path'     => $logoPath,
                'brand_color'   => $data['brand_color']   ?? '#0d9488',
                'sidebar_color' => $data['sidebar_color'] ?? '#0d2238',
                'timezone'      => $data['timezone'],
            ]);

            $userData = [
                'name'      => $data['first_name'] . ' ' . $data['last_name'],
                'email'     => $data['admin_email'],
                'password'  => Hash::make($data['temp_password']),
                'role'      => 'admin',
                'tenant_id' => $tenant->id,
            ];

            if (Schema::hasColumn('users', 'first_name')) {
                $userData['first_name'] = $data['first_name'];
            }
            if (Schema::hasColumn('users', 'last_name')) {
                $userData['last_name'] = $data['last_name'];
            }
            if (Schema::hasColumn('users', 'plain_password')) {
                // Le setter du Model chiffre automatiquement
                $userData['plain_password'] = $data['temp_password'];
            }

            User::create($userData);
        });

        return redirect()->route('superadmin.tenants.index')
            ->with('success', 'Tenant créé avec succès.');
    }

    public function edit(Tenant $tenant)
{
    $tenant->load('admin');
    return view('superadmin.tenants.edit', compact('tenant'));
}

    public function update(Request $request, Tenant $tenant)
    {
        $data = $request->validate([
            'company_name'  => 'required|string|max:100',
            'sector'        => 'nullable|string|max:50',
            'region'        => 'required|string',
            'region_other'  => 'nullable|string|max:100',
            'address'       => 'required|string|max:255',
            'phone'         => 'required|string|max:20',
            'ice'           => 'required|string|size:15',
            'email_societe' => 'required|email|max:100',
            'website'       => 'nullable|url|max:100',
            'logo'          => 'nullable|image|mimes:png,svg,jpg,jpeg|max:2048',
            'brand_color'   => 'nullable|regex:/^#[0-9a-fA-F]{6}$/',
            'sidebar_color' => 'nullable|regex:/^#[0-9a-fA-F]{6}$/',
            'timezone' => 'required|timezone',
            'first_name'    => 'nullable|string|max:50',
            'last_name'     => 'nullable|string|max:50',
            'admin_email'   => 'nullable|email|max:100',
            'temp_password' => 'nullable|string|min:8',
        ]);

        $region = ($data['region'] === 'Autre' && !empty($data['region_other']))
            ? $data['region_other']
            : $data['region'];

        if ($request->hasFile('logo')) {
            if ($tenant->logo_path) Storage::disk('public')->delete($tenant->logo_path);
            $logoPath = $request->file('logo')->store('tenants/logos', 'public');
        } else {
            $logoPath = $tenant->logo_path;
        }

        $tenant->update([
            'name'          => $data['company_name'],
            'sector'        => $data['sector'] ?? null,
            'region'        => $region,
            'address'       => $data['address'],
            'phone'         => $data['phone'],
            'ice'           => $data['ice'],
            'email_societe' => $data['email_societe'],
            'website'       => $data['website'] ?? null,
            'logo_path'     => $logoPath,
            'brand_color'   => $data['brand_color']   ?? $tenant->brand_color   ?? '#0d9488',
            'sidebar_color' => $data['sidebar_color'] ?? $tenant->sidebar_color ?? '#0d2238',
        ]);

        $admin = $tenant->admin;
        if ($admin) {
            $adminUpdates = [];

            if (!empty($data['first_name']) || !empty($data['last_name'])) {
                $firstName = !empty($data['first_name']) ? $data['first_name'] : ($admin->first_name ?? explode(' ', $admin->name)[0] ?? '');
                $lastName  = !empty($data['last_name'])  ? $data['last_name']  : ($admin->last_name  ?? (explode(' ', $admin->name, 2)[1] ?? ''));
                $adminUpdates['name'] = trim($firstName . ' ' . $lastName);

                if (Schema::hasColumn('users', 'first_name')) $adminUpdates['first_name'] = $firstName;
                if (Schema::hasColumn('users', 'last_name'))  $adminUpdates['last_name']  = $lastName;
            }

            if (!empty($data['admin_email'])) {
                $adminUpdates['email'] = $data['admin_email'];
            }

            if (!empty($data['temp_password'])) {
                $adminUpdates['password'] = Hash::make($data['temp_password']);
                if (Schema::hasColumn('users', 'plain_password')) {
                    // Le setter du Model chiffre automatiquement
                    $adminUpdates['plain_password'] = $data['temp_password'];
                }
            }

            if (!empty($adminUpdates)) {
                $admin->update($adminUpdates);
            }
        }

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
