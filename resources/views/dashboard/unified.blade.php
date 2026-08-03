@extends('components.unified-layout')

@section('page-title', 'Tableau de bord')

@section('content')
<div class="max-w-7xl mx-auto">

    <!-- Role Indicator -->
    <div class="mb-8 p-4 bg-gradient-to-r @if(auth()->user()->isSuperAdmin()) from-indigo-50 to-purple-50 border-indigo-200 @else from-teal-50 to-emerald-50 border-teal-200 @endif border rounded-2xl">
        <div class="flex items-center gap-3">
            <div class="role-badge @if(auth()->user()->isSuperAdmin()) superadmin-badge @else admin-badge @endif">
                @if(auth()->user()->isSuperAdmin())
                     Super Administrateur
                @else
                     Administrateur Tenant
                @endif
            </div>
            <div class="text-sm text-gray-600">
                Vue unifiée - Contenu adapté à votre rôle
            </div>
        </div>
    </div>

    <!-- Common Stats Grid -->
    <div class="stats-grid-unified">
        <x-dashboard.stat-card
            label="Employés"
            :value="$totalEmployees"
            color="#10b981"
            subtitle="{{ auth()->user()->isSuperAdmin() ? 'Global' : 'Votre tenant' }}"
            icon="👥"
            icon-bg="green" />

        <x-dashboard.stat-card
            label="Absences en attente"
            :value="$pendingAbsences"
            color="#f59e0b"
            subtitle="À traiter"
            icon="⏳"
            icon-bg="amber" />

        <x-dashboard.stat-card
            label="Salaires ce mois"
            :value="$thisMonthSalaries"
            color="#3b82f6"
            subtitle="Bulletins"
            icon="💰"
            icon-bg="blue" />

        @if(auth()->user()->isSuperAdmin() && isset($totalUsers))
            <x-dashboard.stat-card
                label="Utilisateurs"
                :value="$totalUsers"
                color="#8b5cf6"
                subtitle="Tous tenants"
                icon="👤"
                icon-bg="purple" />
        @endif
    </div>

    @if(auth()->user()->isSuperAdmin())
        <!-- SuperAdmin Specific: Tenant Management -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">

            <!-- Tenant Stats -->
            <div class="content-section">
                <h2 class="text-2xl font-bold text-gray-900 mb-6 flex items-center gap-3">
                    📊 Statistiques Tenants
                </h2>
                <div class="stats-grid-unified">
                    <x-dashboard.stat-card
                        label="Total Tenants"
                        :value="$tenantStats['total_tenants'] ?? 0"
                        color="#6366f1" />
                    <x-dashboard.stat-card
                        label="Actifs"
                        :value="$tenantStats['active_tenants'] ?? 0"
                        color="#10b981" />
                    <x-dashboard.stat-card
                        label="En essai"
                        :value="$tenantStats['trial_tenants'] ?? 0"
                        color="#f59e0b" />
                </div>
            </div>

            <!-- Recent Tenants Table -->
            <div class="content-section">
                <x-dashboard.recent-table
                    title="Tenants récents"
                    :headers="['Société', 'Plan', 'Statut', 'Admin', 'Créé']"
                    :items="$recentTenants ?? collect()"
                    link="{{ route('superadmin.tenants.index') }}" />
            </div>
        </div>

    @else
        <!-- Tenant Admin Specific -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">

            <!-- Recent Employees -->
            <div class="content-section">
                <x-dashboard.recent-table
                    title="Employés récents"
                    :headers="['Nom', 'Département', 'Poste', 'Date d\'embauche']"
                    :items="$recentEmployees ?? collect()"
                    link="{{ route('employees.index') }}" />
            </div>

            <!-- Recent Absences -->
            <div class="content-section">
                <x-dashboard.recent-table
                    title="Demandes récentes"
                    :headers="['Employé', 'Type', 'Dates', 'Statut']"
                    :items="$recentAbsences ?? collect()"
                    link="{{ route('absences.index') }}" />
            </div>
        </div>
    @endif

</div>
@endsection
