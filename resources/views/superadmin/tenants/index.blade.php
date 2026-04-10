@extends('layouts.superadmin')

@section('title', 'Gestion des Tenants')
@section('page-title', 'Gestion des Tenants')

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/@tailwindcss/forms@^0.5/dist/forms.css" rel="stylesheet">
<style>
.table-container { overflow-x: auto; }
.table { width: 100%; border-collapse: separate; border-spacing: 0; }
.table th { position: sticky; top: 0; background: white; z-index: 10; }
.status-badge { @apply inline-flex items-center gap-1 px-2 py-1 rounded-full text-xs font-medium border; }
.plan-badge { @apply px-2 py-1 rounded-md text-xs font-medium; }
</style>
@endpush

@section('content')
<div class="space-y-6">
    {{-- KPI Stats --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-6">
        <x-dashboard.kpi-card label="Total Tenants" :value="$counts['all'] ?? Tenant::count()" value-color="#6366f1" icon="🏢" />
        <x-dashboard.kpi-card label="Actifs" :value="$counts['active'] ?? Tenant::active()->count()" value-color="#10b981" icon="✅" />
        <x-dashboard.kpi-card label="En Essai" :value="$counts['trial'] ?? Tenant::byStatus('trial')->count()" value-color="#3b82f6" icon="🔬" />
        <x-dashboard.kpi-card label="Suspendus" :value="$counts['suspended'] ?? Tenant::byStatus('suspended')->count()" value-color="#f59e0b" icon="⏸️" />
        <x-dashboard.kpi-card label="Inactifs" :value="$counts['inactive'] ?? Tenant::byStatus('inactive')->count()" value-color="#6b7280" icon="❌" />
    </div>

    {{-- Toolbar --}}
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 bg-white rounded-2xl shadow-lg border border-gray-100 p-6">
        <div class="flex flex-wrap items-center gap-3">
            <form method="GET" class="flex items-center gap-2 bg-gray-50 rounded-xl px-4 py-2 border">
                <svg class="w-5 h-5 text-gray-400" fill="none" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Rechercher par nom, slug..."
                       class="bg-transparent outline-none text-sm w-64">
            </form>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('superadmin.tenants.index') }}" class="px-4 py-2 text-xs font-semibold rounded-lg {{ !request('status') ? 'bg-indigo-100 text-indigo-700' : 'text-gray-500 hover:bg-gray-100' }}">Tous</a>
                <a href="{{ route('superadmin.tenants.index', ['status' => 'active']) }}" class="status-badge bg-green-100 text-green-800 border-green-200 {{ request('status') == 'active' ? 'bg-green-500 text-white border-green-500' : '' }}">Actifs</a>
                <a href="{{ route('superadmin.tenants.index', ['status' => 'trial']) }}" class="status-badge bg-blue-100 text-blue-800 border-blue-200 {{ request('status') == 'trial' ? 'bg-blue-500 text-white border-blue-500' : '' }}">Essai</a>
                <a href="{{ route('superadmin.tenants.index', ['status' => 'suspended']) }}" class="status-badge bg-yellow-100 text-yellow-800 border-yellow-200 {{ request('status') == 'suspended' ? 'bg-yellow-500 text-white border-yellow-500' : '' }}">Suspendus</a>
            </div>
        </div>
        <a href="{{ route('superadmin.tenants.create') }}" class="btn bg-gradient-to-r from-indigo-600 to-purple-600 text-white px-6 py-2.5 rounded-xl shadow-lg hover:shadow-xl transition-all font-semibold flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
            </svg>
            Nouveau Tenant
        </a>
    </div>

    {{-- Tenants Table --}}
    <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
        <div class="table-container">
            <table class="table">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Entreprise</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Admin</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Utilisateurs</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Plan</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Statut</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Créé le</th>
                        <th class="px-6 py-4 text-right text-xs font-semibold text-gray-600 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($tenants as $tenant)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-5">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-xl flex items-center justify-center text-sm font-bold" style="background-color: {{ $tenant->brand_color }}; color: white;">
                                        {{ $tenant->initials }}
                                    </div>
                                    <div>
                                        <div class="font-semibold text-gray-900">{{ $tenant->name }}</div>
                                        <div class="text-sm text-gray-500 font-mono">{{ $tenant->domain }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-5">
                                <span class="text-sm font-medium text-gray-900">{{ $tenant->admin?->name ?? 'Non assigné' }}</span>
                            </td>
                            <td class="px-6 py-5">
                                <span class="text-sm font-semibold text-gray-900">{{ $tenant->users_count }}</span>
                            </td>
                            <td class="px-6 py-5">
                                <span class="plan-badge {{ $tenant->plan->badgeClass() }}">{{ $tenant->plan->label() }}</span>
                            </td>
                            <td class="px-6 py-5">
                                <span class="status-badge {{ $tenant->status->dotClass() }}">
                                    {{ $tenant->status->label() }}
                                </span>
                            </td>
                            <td class="px-6 py-5 text-sm text-gray-500">
                                {{ $tenant->created_at->format('d/m/Y') }}
                            </td>
                            <td class="px-6 py-5 text-right space-x-2">
                                <a href="{{ route('superadmin.tenants.show', $tenant) }}" class="text-indigo-600 hover:text-indigo-900 text-sm font-medium">Détails</a>
                                <a href="{{ route('superadmin.tenants.edit', $tenant) }}" class="text-green-600 hover:text-green-900 text-sm font-medium">Modifier</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center text-gray-500">
                                <svg class="w-16 h-16 mx-auto mb-4 opacity-50" fill="none" viewBox="0 0 24 24">
                                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V7m-9 5h6m-6 0H5" />
                                </svg>
                                <h3 class="text-lg font-semibold mb-1">Aucun tenant trouvé</h3>
                                <p class="mb-4">Commencez par créer votre premier tenant.</p>
                                <a href="{{ route('superadmin.tenants.create') }}" class="bg-indigo-600 text-white px-6 py-2 rounded-xl font-semibold hover:bg-indigo-700 transition">Créer un tenant</a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-6 py-4 bg-gray-50 border-t border-gray-100">
            {{ $tenants->appends(request()->query())->links() }}
        </div>
    </div>

    {{-- Recent Tenants Sidebar (lg+ ) --}}
    <div class="lg:grid lg:grid-cols-4 lg:gap-6 lg:col-span-1 hidden">
        <x-dashboard.recent-list title="Tenants Récents" :items="$recentTenants ?? Tenant::latest()->take(5)->pluck('name')" :view-all-url="route('superadmin.tenants.index')" />
    </div>
</div>
@endsection
