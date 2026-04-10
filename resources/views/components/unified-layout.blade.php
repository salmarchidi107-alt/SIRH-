<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', config('app.name', 'HospitalRH')) — {{ config('app.name', 'HospitalRH') }}</title>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    @stack('styles')
    <style>
        /* Unified layout enhancements */
        .sidebar { width: 260px; transition: width 0.3s ease; }
        .sidebar.collapsed { width: 60px; }
        .nav-item { display: flex; align-items: center; gap: 12px; padding: 10px 16px; color: #94a3b8; text-decoration: none; border-radius: 8px; transition: all 0.2s; }
        .nav-item:hover { background: rgba(255,255,255,0.1); color: #fff; }
        .nav-item.active { background: rgba(79,70,229,0.3); color: #a5b4fc; }
        .brand-icon-custom { width: 40px; height: 40px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 16px; font-weight: 700; color: #fff; flex-shrink: 0; }
    </style>
</head>
<body class="bg-gray-50">
<div class="app-wrapper flex min-h-screen">

    <!-- Full Sidebar (always expanded) -->
    <aside class="sidebar bg-gradient-to-b from-gray-900 to-gray-800 text-white border-r border-gray-700">
        <div class="sidebar-header p-4 border-b border-gray-700">
            @php
                $tenant = auth()->user()?->tenant;
                $brandColor = $tenant?->brand_color ?? '#14b8a6';
                $appName = $tenant?->name ?? config('app.name', 'HospitalRH');
                $initials = $tenant?->initials ?? strtoupper(substr($appName, 0, 1));
                $logoPath = $tenant?->logo_path;
            @endphp
            <a href="{{ route('dashboard.unified') }}" class="brand flex items-center gap-3">
                @if($logoPath)
                    <img src="{{ Storage::url($logoPath) }}" alt="Logo" class="w-10 h-10 object-contain rounded-lg">
                @else
                    <div class="brand-icon-custom" style="background: {{ $brandColor }};">{{ $initials }}</div>
                @endif
                <div>
                    <div class="font-semibold text-white">{{ $appName }}</div>
                    <div class="text-xs opacity-75">Gestion RH</div>
                @if(auth()->user()?->isSuperAdmin())
                    <span class="inline-block ml-2 px-2 py-1 bg-indigo-500 text-white text-xs rounded-full font-medium">Super Admin</span>
                @endif
                </div>
            </a>
        </div>

        <nav class="sidebar-nav flex-1 p-4 space-y-2 overflow-y-auto">
            <!-- SuperAdmin Section (top if applicable) -->
            @if(auth()->user()?->isSuperAdmin())
                <div class="nav-section-label text-xs font-semibold text-indigo-300 uppercase tracking-wider mb-3">Super Admin</div>
                <a href="{{ route('superadmin.dashboard') }}" class="nav-item {{ request()->routeIs('superadmin.dashboard') ? 'active' : '' }}">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25"/>
                    </svg>
                    Gestion Tenants
                </a>
            @endif

            <!-- Common Dashboard -->
            <a href="{{ route('dashboard.unified') }}" class="nav-item {{ request()->routeIs('dashboard.unified') ? 'active' : '' }}">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                </svg>
                Tableau de Bord
            </a>

            <!-- Tenant Admin / RH Sections -->
@if(!auth()->user()?->isSuperAdmin() && in_array(auth()->user()->role, ['admin', 'rh']))
                <div class="nav-section-label text-xs font-semibold text-teal-300 uppercase tracking-wider mb-3 mt-4">Personnel</div>
            @endif

            <!-- Menu principal -->
            <div class="nav-section-label text-xs font-semibold text-blue-300 uppercase tracking-wider mb-3 mt-4">Principal</div>
            <a href="{{ route('dashboard.unified') }}" class="nav-item {{ request()->routeIs('dashboard.unified') ? 'active' : '' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor">
                    <rect x="3" y="3" width="7" height="7" rx="1"/>
                    <rect x="14" y="3" width="7" height="7" rx="1"/>
                    <rect x="14" y="14" width="7" height="7" rx="1"/>
                    <rect x="3" y="14" width="7" height="7" rx="1"/>
                </svg>
                Tableau de bord
            </a>

            {{-- Simplified - expand as needed --}}
            @php $tenant = auth()->user()->tenant; @endphp
        </nav>

        <!-- User Footer -->
        <div class="sidebar-footer p-4 border-t border-gray-700 mt-auto">
            <div class="user-card flex items-center gap-3 p-3 bg-gray-800/50 rounded-lg">
                <div class="user-avatar w-10 h-10 bg-indigo-500 rounded-full flex items-center justify-center font-semibold text-white text-sm">
                    {{ substr(auth()->user()->name ?? '', 0, 1) }}
                </div>
                <div class="user-info min-w-0 flex-1">
                    <div class="user-name font-semibold text-white truncate">{{ auth()->user()->name }}</div>
                    <div class="user-role text-xs opacity-75">{{ auth()->user()->getRoleDisplayName() }}</div>
                </div>
            </div>
            <form method="POST" action="{{ route('logout') }}" class="mt-3">
                @csrf
                <button type="submit" class="w-full flex items-center justify-center gap-2 text-sm text-gray-300 hover:text-white py-2 px-3 rounded-lg hover:bg-gray-700 transition">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7"/>
                    </svg>
                    Déconnexion
                </button>
            </form>
        </div>
    </aside>

    <!-- Main Content -->
    <div class="flex-1 flex flex-col">
        <!-- Topbar -->
        <header class="topbar bg-white border-b border-gray-200 shadow-sm px-6 py-4 flex items-center gap-4">
            <div class="topbar-title text-xl font-semibold text-gray-900 flex-1">@yield('page-title', 'Tableau de bord')</div>
            <!-- Notifications dropdown (copy logic from app.blade.php) -->
            <div class="relative">
                <button id="notifBtn" class="p-2 text-gray-500 hover:text-gray-900 rounded-lg hover:bg-gray-100">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                    </svg>
                </button>
            </div>
        </header>

        <!-- Page Content -->
        <main class="flex-1 p-8 overflow-y-auto">
            @if (session('success'))
                <div class="mb-6 p-4 bg-green-50 border border-green-200 rounded-lg">
                    {{ session('success') }}
                </div>
            @endif
            @yield('content')
        </main>
    </div>
</div>

@stack('scripts')

