@extends('layouts.superadmin')

@section('title', $tenant->name)
@section('page-title', $tenant->name)

@section('page-header')
    <div class="flex items-center gap-6">
        <div class="w-20 h-20 rounded-2xl flex items-center justify-center text-xl font-bold shadow-2xl border-4 border-white" style="background-color: {{ $tenant->brand_color }}; color: white;">
            {{ $tenant->initials }}
        </div>
        <div>
            <div class="text-4xl font-black text-gray-900 mb-2">{{ $tenant->name }}</div>
            <div class="flex items-center gap-4 text-sm text-gray-600 mb-2">
                <span class="font-mono bg-gray-100 px-3 py-1 rounded-full">{{ $tenant->domain }}</span>
                <span class="flex items-center gap-1 px-3 py-1 rounded-full bg-gray-100">
                    <div class="w-2 h-2 rounded-full bg-green-500"></div>
                    {{ $tenant->status->label() }}
                </span>
                <span class="plan-badge {{ $tenant->plan->badgeClass() }}">{{ $tenant->plan->label() }}</span>
            </div>
            <div class="text-sm text-gray-500">Créé le {{ $tenant->created_at->format('d/m/Y') }} • {{ $tenant->users_count }} utilisateurs</div>
        </div>
    </div>
@endsection

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
    {{-- Main Stats --}}
    <div class="lg:col-span-2 space-y-6">
        {{-- KPI Grid --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <x-dashboard.kpi-card label="Utilisateurs" :value="$tenant->users_count" value-color="#8b5cf6" icon="👥" />
            <x-dashboard.kpi-card label="Employés" value="247" value-color="#10b981" icon="👷" />
            <x-dashboard.kpi-card label="Absences" value="23" value-color="#f59e0b" icon="📅" />
            <x-dashboard.kpi-card label="Salaires" value="€47.2k" value-color="#ef4444" icon="💰" />
            <x-dashboard.kpi-card label="Revenu MR" value="€2.4k" value-color="#3b82f6" icon="📈" />
            <x-dashboard.kpi-card label="Util. Stockage" value="42%" value-color="#06b6d4" icon="💾" />
        </div>

        {{-- Recent Activity --}}
        <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
            <x-dashboard.recent-table title="Activité Récente">
                <tr><td>Ahmed El Amrani</td><td>Demandé 3 jours CP</td><td>Il y a 2h</td></tr>
                <tr><td>Fatima Zahra</td><td>Planning hebdo publié</td><td>Il y a 1j</td></tr>
                <tr><td>Salaire généré</td><td>Octobre 2024 (47 salariés)</td><td>Il y a 3j</td></tr>
            </x-dashboard.recent-table>
        </div>
    </div>

    {{-- Sidebar Actions --}}
    <div class="space-y-6">
        <div class="bg-gradient-to-br from-indigo-50 to-purple-50 rounded-2xl p-6 border-2 border-indigo-100">
            <h3 class="font-bold text-lg text-gray-900 mb-4">Actions Rapides</h3>
            <div class="space-y-3">
                <a href="{{ route('superadmin.tenants.edit', $tenant) }}" class="block w-full bg-white hover:bg-indigo-50 border border-indigo-200 rounded-xl p-4 text-left transition-all hover:shadow-md">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-indigo-100 rounded-xl flex items-center justify-center">
                            <svg class="w-5 h-5 text-indigo-600" fill="none" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H8a2 2 0 00-2 2v6a2 2 0 002 2H11m4 0h2a2 2 0 002-2V7a2 2 0 00-2-2h-2z"/>
                            </svg>
                        </div>
                        <div>
                            <div class="font-semibold text-gray-900">Modifier le tenant</div>
                            <div class="text-sm text-gray-600">Plan, statut, branding...</div>
                        </div>
                    </div>
                </a>
                @if($tenant->status->isActive())
                    <form method="POST" action="{{ route('superadmin.tenants.suspend', $tenant) }}" class="block w-full bg-white hover:bg-yellow-50 border border-yellow-200 rounded-xl p-4 text-left transition-all hover:shadow-md">
                        @csrf
                        <button type="submit" class="w-full flex items-center gap-3 text-left hover:no-underline p-0 bg-transparent border-none">
                            <div class="w-10 h-10 bg-yellow-100 rounded-xl flex items-center justify-center">
                                <svg class="w-5 h-5 text-yellow-600" fill="none" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                            <div>
                                <div class="font-semibold text-gray-900">Suspendre le tenant</div>
                                <div class="text-sm text-gray-600">Accès temporairement bloqué</div>
                            </div>
                        </button>
                    </form>
                @else
                    <form method="POST" action="{{ route('superadmin.tenants.reactiviate', $tenant) }}" class="block w-full bg-white hover:bg-green-50 border border-green-200 rounded-xl p-4 text-left transition-all hover:shadow-md">
                        @csrf
                        <button type="submit" class="w-full flex items-center gap-3 text-left hover:no-underline p-0 bg-transparent border-none">
                            <div class="w-10 h-10 bg-green-100 rounded-xl flex items-center justify-center">
                                <svg class="w-5 h-5 text-green-600" fill="none" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                            </div>
                            <div>
                                <div class="font-semibold text-gray-900">Réactiver le tenant</div>
                                <div class="text-sm text-gray-600">Restaurer l'accès complet</div>
                            </div>
                        </button>
                    </form>
                @endif
            </div>
        </div>

        {{-- Usage Metrics --}}
        <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6 space-y-4">
            <h4 class="font-bold text-gray-900 text-lg">Utilisation</h4>
            <div class="space-y-4">
                <div>
                    <div class="flex justify-between text-sm mb-2">
                        <span>Stockage</span>
                        <span>42%</span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-2">
                        <div class="bg-gradient-to-r from-green-500 to-green-600 h-2 rounded-full" style="width: 42%"></div>
                    </div>
                </div>
                <div>
                    <div class="flex justify-between text-sm mb-2">
                        <span>Appels API</span>
                        <span>1.2k / 10k</span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-2">
                        <div class="bg-gradient-to-r from-blue-500 to-blue-600 h-2 rounded-full" style="width: 67%"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
</xai:function_call >

<xai:function_call name="edit_file">
<parameter name="path">d:/Projects/HospitalRh/TODO.md
