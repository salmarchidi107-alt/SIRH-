<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Enums\TenantPlan;
use App\Enums\TenantStatus;
use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;

class TenantController extends Controller
{
    // ─── Index ────────────────────────────────────────────────────────────────

    public function index(Request $request)
    {
        $query = Tenant::with('admin')->withCount('users');

        if ($s = $request->search) {
            $query->where(fn($q) => $q
                ->where('name',   'like', "%{$s}%")
                ->orWhere('slug', 'like', "%{$s}%")
                ->orWhere('sector', 'like', "%{$s}%")
            );
        }

        if ($status = $request->status) {
            $query->byStatus($status);
        }

        $sort = $request->sort ?? 'created_at';
        $query->orderByDesc(
            $sort === 'users'
                ? DB::raw('(SELECT COUNT(*) FROM users WHERE tenant_id = tenants.id)')
                : $sort
        );

        $tenants = $query->paginate(20)->withQueryString();

        $counts = [
            'all'       => Tenant::count(),
            'active'    => Tenant::byStatus('active')->count(),
            'suspended' => Tenant::byStatus('suspended')->count(),
            'trial'     => Tenant::byStatus('trial')->count(),
            'inactive'  => Tenant::byStatus('inactive')->count(),
        ];

        return view('superadmin.tenants.stats', compact('tenants', 'counts'));
    }

    // ─── Create ───────────────────────────────────────────────────────────────

    public function create()
    {
        return view('superadmin.tenants.create', [
            'plans'    => TenantPlan::cases(),
            'statuses' => TenantStatus::cases(),
        ]);
    }

    // ─── Store ────────────────────────────────────────────────────────────────

    public function store(Request $request)
{
    $data = $request->validate([
        'company_name'  => 'required|string|max:100',
        'slug'          => 'required|string|max:50|unique:tenants,slug|regex:/^[a-z0-9\-]+$/',
        'sector'        => 'nullable|string|max:50',
        'logo'          => 'nullable|image|max:2048',
        'brand_color'   => 'required|regex:/^#[0-9a-fA-F]{6}$/',
        'sidebar_color' => 'required|regex:/^#[0-9a-fA-F]{6}$/', // ← AJOUTÉ
        'plan'          => ['required', Rule::enum(TenantPlan::class)],
        'status'        => ['required', Rule::enum(TenantStatus::class)],
        'region'        => 'required|string',
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
            'logo_path'     => $logoPath,
            'brand_color'   => $data['brand_color'],
            'sidebar_color' => $data['sidebar_color'], // ← AJOUTÉ
            'plan'          => $data['plan'],
            'status'        => $data['status'],
            'region'        => $data['region'],
            'database_name' => 'tenant_' . str_replace('-', '_', $data['slug']),
        ]);

        $domainName   = $data['slug'] . '.hospitalrh.test';
        $domainRecord = DB::table('domains')->where('domain', $domainName)->first();

        if ($domainRecord) {
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

    // ─── Update ───────────────────────────────────────────────────────────────

    public function update(Request $request, Tenant $tenant)
    {
        $data = $request->validate([
            'company_name' => 'required|string|max:100',
            'slug'         => ['required', 'regex:/^[a-z0-9\-]+$/',
                               Rule::unique('tenants', 'slug')->ignore($tenant->id)],
            'sector'       => 'nullable|string|max:50',
            'logo'         => 'nullable|image|max:2048',
            'brand_color'  => 'required|regex:/^#[0-9a-fA-F]{6}$/',
            'plan'         => ['required', Rule::enum(TenantPlan::class)],
            'status'       => ['required', Rule::enum(TenantStatus::class)],
        ]);

        if ($request->hasFile('logo')) {
            if ($tenant->logo_path) {
                Storage::disk('public')->delete($tenant->logo_path);
            }
            $logoPath = $request->file('logo')->store('tenants/logos', 'public');
        } else {
            $logoPath = $tenant->logo_path;
        }

        $tenant->update([
            'name'        => $data['company_name'],
            'slug'        => $data['slug'],
            'sector'      => $data['sector'] ?? null,
            'logo_path'   => $logoPath,
            'brand_color' => $data['brand_color'],
            'plan'        => $data['plan'],
            'status'      => $data['status'],
        ]);

        return back()->with('success', 'Tenant mis à jour.');
    }

    // ─── Destroy ──────────────────────────────────────────────────────────────

    public function destroy(Tenant $tenant)
    {
        if ($tenant->logo_path) {
            Storage::disk('public')->delete($tenant->logo_path);
        }

        User::where('tenant_id', $tenant->id)->delete();
        $tenant->delete();

        return redirect()->route('superadmin.tenants.index')
            ->with('success', 'Tenant supprimé.');
    }

    // ─── Suspend / Reactivate ─────────────────────────────────────────────────

    public function suspend(Tenant $tenant)
    {
        $tenant->update(['status' => TenantStatus::Suspended->value]);
        return back()->with('success', "Tenant \"{$tenant->name}\" suspendu.");
    }

    public function reactivate(Tenant $tenant)
    {
        $tenant->update(['status' => TenantStatus::Active->value]);
        return back()->with('success', "Tenant \"{$tenant->name}\" réactivé.");
    }

    public function edit(Tenant $tenant)
    {
        $plans = TenantPlan::cases();
        $statuses = TenantStatus::cases();

        return view('superadmin.tenants.edit', compact('tenant', 'plans', 'statuses'));
    }

    // ─── Show ─────────────────────────────────────────────────────────────────

    public function show(Tenant $tenant)
    {
        $tenant->load('admin')->withCount('users');
        $plans = TenantPlan::cases();
        $statuses = TenantStatus::cases();

        return view('superadmin.tenants.show', compact('tenant', 'plans', 'statuses'));
    }
}
