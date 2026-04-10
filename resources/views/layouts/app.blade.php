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
        .nav-submenu  { padding-left: 20px; margin: 4px 0; }
        .nav-sublink  { display: block; padding: 6px 12px; font-size: 0.85rem; color: #888; border-radius: 6px; text-decoration: none; margin: 2px 0; transition: all 0.2s; }
        .nav-sublink:hover { background: rgba(255,255,255,0.1); color: #fff; }
        .nav-sublink.active { background: var(--primary); color: #fff; }
        .brand-icon-custom { width: 36px; height: 36px; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: 15px; font-weight: 700; color: #fff; flex-shrink: 0; }
        .notif-dot { position: absolute; top: 6px; right: 6px; width: 8px; height: 8px; background: #ef4444; border-radius: 50%; border: 2px solid #fff; }
        .nav-badge-live {
            margin-left: auto;
            background: #0d9488;
            color: #fff;
            font-size: 10px;
            font-weight: 700;
            padding: 1px 7px;
            border-radius: 20px;
            line-height: 1.6;
        }
    </style>
</head>
<body>
<div class="app-wrapper">

    {{-- ═══════════════════════════════════════════════════════════ SIDEBAR ═══ --}}
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-header">
            @php
                $tenant     = auth()->user()?->tenant;
                $brandColor = $tenant?->brand_color ?? '#14b8a6';
                $appName    = $tenant?->name ?? config('app.name', 'HospitalRH');
                $appSub     = 'Gestion RH';
                $logoPath   = $tenant?->logo_path;
                $initials   = $tenant?->initials ?? strtoupper(substr($appName, 0, 1));

                // ✅ Route dashboard selon le rôle
                $dashboardHref = route('admin.dashboard');
                if (auth()->check()) {
                    $role = auth()->user()->role;
                    if ($role === 'superadmin')          $dashboardHref = route('superadmin.dashboard');
                    elseif (in_array($role, ['admin', 'rh'])) $dashboardHref = route('admin.dashboard');
                    else                                 $dashboardHref = route('employee.dashboard');
                }
            @endphp

            {{-- ✅ Lien brand corrigé --}}
            <a href="{{ $dashboardHref }}" style="display:flex;align-items:center;gap:10px;text-decoration:none;">
                @if($logoPath)
                    <img src="{{ Storage::url($logoPath) }}" alt="logo"
                         style="width:36px;height:36px;object-fit:contain;border-radius:8px;flex-shrink:0;">
                @else
                    <div class="brand-icon-custom" style="background:{{ $brandColor }};">
                        {{ $initials }}
                    </div>
                @endif
                <div class="brand-name">
                    {{ $appName }}
                    <span>{{ $appSub }}</span>
                </div>
            </a>
        </div>

        <nav class="sidebar-nav">

            {{-- ── SuperAdmin section ──────────────────────────────────────── --}}
            @if(Auth::check() && Auth::user()->role === 'superadmin')
            <a href="{{ route('superadmin.dashboard') }}"
               class="nav-item {{ request()->routeIs('superadmin.dashboard') ? 'active' : '' }}">
                <svg class="nav-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25"/>
                </svg>
                Dashboard SuperAdmin
            </a>
            @endif

            {{-- ── Principal (tous les rôles) ──────────────────────────────── --}}
            <div class="nav-section-label">Principal</div>

            <a href="{{ $dashboardHref }}"
               class="nav-item {{ request()->routeIs(['admin.dashboard', 'employee.dashboard', 'superadmin.dashboard']) ? 'active' : '' }}">
                <svg class="nav-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <rect x="3" y="3" width="7" height="7" rx="1"/>
                    <rect x="14" y="3" width="7" height="7" rx="1"/>
                    <rect x="14" y="14" width="7" height="7" rx="1"/>
                    <rect x="3" y="14" width="7" height="7" rx="1"/>
                </svg>
                Tableau de bord
            </a>

            {{-- ── Profil (employee uniquement) ───────────────────────────── --}}
@if(Auth::check() && Auth::user()->role === 'employee')
            <a href="{{ route('profile') }}" class="nav-item {{ request()->routeIs('profile') ? 'active' : '' }}">
                <svg class="nav-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                    <circle cx="12" cy="7" r="4"/>
                </svg>
                Mon Profil
            </a>
            @endif

            {{-- ── Personnel (admin / rh) ───────────────────────────────────── --}}
            @if(Auth::check() && in_array(Auth::user()->role, ['admin', 'rh']))
            <div class="nav-section-label">Personnel</div>

            <a href="{{ route('employees.index') }}"
               class="nav-item {{ request()->routeIs('employees.*') ? 'active' : '' }}">
                <svg class="nav-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                    <circle cx="9" cy="7" r="4"/>
                    <path d="M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/>
                </svg>
                Liste du Personnel
            </a>

            <a href="{{ route('trombinoscope') }}"
               class="nav-item {{ request()->routeIs('trombinoscope') ? 'active' : '' }}">
                <svg class="nav-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <rect x="3" y="3" width="18" height="18" rx="2"/>
                    <circle cx="9" cy="10" r="2"/><circle cx="15" cy="10" r="2"/>
                    <path d="M6 16c0-1.1 1.3-2 3-2s3 .9 3 2M12 16c0-1.1 1.3-2 3-2s3 .9 3 2"/>
                </svg>
                Trombinoscope
            </a>

            <a href="{{ route('news.index') }}"
               class="nav-item {{ request()->routeIs('news.*') ? 'active' : '' }}">
                <svg class="nav-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/>
                    <path d="M13.73 21a2 2 0 0 1-3.46 0"/>
                </svg>
                Actualités
            </a>
            @endif

            {{-- ── Mon Espace (employee) ────────────────────────────────────── --}}
            @if(Auth::check() && Auth::user()->role === 'employee')
            <div class="nav-section-label">Mon Espace</div>

            <a href="{{ route('trombinoscope') }}"
               class="nav-item {{ request()->routeIs('trombinoscope') ? 'active' : '' }}">
                <svg class="nav-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <rect x="3" y="3" width="18" height="18" rx="2"/>
                    <circle cx="9" cy="10" r="2"/><circle cx="15" cy="10" r="2"/>
                    <path d="M6 16c0-1.1 1.3-2 3-2s3 .9 3 2M12 16c0-1.1 1.3-2 3-2s3 .9 3 2"/>
                </svg>
                Trombinoscope
            </a>

            <a href="{{ route('planning.show', ['employee' => Auth::user()->employee_id ?? 0]) }}"
               class="nav-item {{ request()->routeIs('planning.show') ? 'active' : '' }}">
                <svg class="nav-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                    <line x1="16" y1="2" x2="16" y2="6"/>
                    <line x1="8" y1="2" x2="8" y2="6"/>
                    <line x1="3" y1="10" x2="21" y2="10"/>
                </svg>
                Mon Planning
            </a>
            @endif

            {{-- ── Temps & Présence (admin / rh) ───────────────────────────── --}}
            @if(Auth::check() && in_array(Auth::user()->role, ['admin', 'rh']))
            <div class="nav-section-label">Temps & Présence</div>

            <a href="{{ route('planning.weekly') }}"
               class="nav-item {{ request()->routeIs('planning.*') ? 'active' : '' }}">
                <svg class="nav-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                    <line x1="16" y1="2" x2="16" y2="6"/>
                    <line x1="8" y1="2" x2="8" y2="6"/>
                    <line x1="3" y1="10" x2="21" y2="10"/>
                </svg>
                Planning
            </a>

            <a href="{{ route('temps.vue-ensemble') }}"
               class="nav-item {{ request()->routeIs('temps.vue-ensemble') ? 'active' : '' }}">
                <svg class="nav-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="10"/>
                    <polyline points="12 6 12 12 16 14"/>
                </svg>
                Vue d'ensemble
            </a>

            @php
                $pointageEnAttente = 0;
                try {
                    $pointageEnAttente = \App\Models\Pointage::forDate(today()->toDateString())
                        ->where('valide', false)
                        ->where('statut', 'present')
                        ->count();
                } catch (\Exception $e) {}
            @endphp

            <a href="{{ route('pointage.index') }}"
               class="nav-item {{ request()->routeIs('pointage.*') ? 'active' : '' }}"
               style="display:flex;align-items:center;gap:10px;">
                <svg class="nav-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <rect x="3" y="3" width="8" height="18" rx="1.5"/>
                    <rect x="13" y="3" width="8" height="8" rx="1.5"/>
                    <rect x="13" y="13" width="8" height="8" rx="1.5"/>
                </svg>
                Pointage
                @if($pointageEnAttente > 0)
                <span class="nav-badge-live">{{ $pointageEnAttente }}</span>
                @endif
            </a>
            @endif

            {{-- ── Absences & Congés ────────────────────────────────────────── --}}
            <div class="nav-section-label">Absences & Congés</div>

            <a href="{{ route('absences.index') }}"
               class="nav-item {{ request()->routeIs('absences.index') || request()->routeIs('absences.show') ? 'active' : '' }}">
                <svg class="nav-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path d="M9 11l3 3L22 4"/>
                    <path d="M21 12v7a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2h11"/>
                </svg>
                Liste des demandes
            </a>

            @if(Auth::check() && in_array(Auth::user()->role, ['admin', 'rh']))
            <a href="{{ route('absences.calendar') }}"
               class="nav-item {{ request()->routeIs('absences.calendar') ? 'active' : '' }}">
                <svg class="nav-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                    <line x1="16" y1="2" x2="16" y2="6"/>
                    <line x1="8" y1="2" x2="8" y2="6"/>
                    <line x1="3" y1="10" x2="21" y2="10"/>
                </svg>
                État visuel des absences
            </a>

            <a href="{{ route('absences.counters') }}"
               class="nav-item {{ request()->routeIs('absences.counters') ? 'active' : '' }}">
                <svg class="nav-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <line x1="18" y1="20" x2="18" y2="10"/>
                    <line x1="12" y1="20" x2="12" y2="4"/>
                    <line x1="6"  y1="20" x2="6"  y2="14"/>
                </svg>
                Compteurs et droits d'absences
            </a>
            @endif

            {{-- ── Paie (admin / rh) ───────────────────────────────────────── --}}
            @if(Auth::check() && in_array(Auth::user()->role, ['admin', 'rh']))
            <div class="nav-section-label">Paie</div>

            <a href="{{ route('salary.index') }}"
               class="nav-item {{ request()->routeIs('salary.index') || request()->routeIs('salary.show') || request()->routeIs('salary.create') ? 'active' : '' }}">
                <svg class="nav-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <line x1="12" y1="1" x2="12" y2="23"/>
                    <path d="M17 5H9.5a3.5 3.5 0 00 0 7h5a3.5 3.5 0 01 0 7H6"/>
                </svg>
                Salaires
            </a>
            @endif

            {{-- ── Paie (employee — son propre bulletin) ──────────────────── --}}
            @if(Auth::check() && Auth::user()->role === 'employee' && Auth::user()->employee_id)
            <div class="nav-section-label">Paie</div>

            <a href="{{ route('salary.show', Auth::user()->employee_id) }}"
               class="nav-item {{ request()->routeIs('salary.show') ? 'active' : '' }}">
                <svg class="nav-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <line x1="12" y1="1" x2="12" y2="23"/>
                    <path d="M17 5H9.5a3.5 3.5 0 00 0 7h5a3.5 3.5 0 01 0 7H6"/>
                </svg>
                Mon Salaire
            </a>
            @endif

            {{-- ── Configuration (admin tenant) ───────────────────────────── --}}
            @if(Auth::check() && Auth::user()->role === 'admin')
            <div class="nav-section-label">Paramètres</div>
            @endif

        </nav>

        {{-- ── Footer sidebar ─────────────────────────────────────────────── --}}
        <div class="sidebar-footer">
            @auth
            <div class="user-card">
                <div class="user-avatar">{{ substr(Auth::user()->name, 0, 1) }}</div>
                <div class="user-info">
                    <div class="user-name">{{ Auth::user()->name }}</div>
                    <div class="user-role">{{ Auth::user()->getRoleDisplayName() }}</div>
                </div>
            </div>
            <form method="POST" action="{{ route('logout') }}" class="mt-2">
                @csrf
                <button type="submit" class="btn btn-sm btn-outline w-100"
                        style="color:#fff;border-color:rgba(255,255,255,0.3);display:flex;align-items:center;gap:6px;">
                    <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
                        <polyline points="16 17 21 12 16 7"/>
                        <line x1="21" y1="12" x2="9" y2="12"/>
                    </svg>
                    Déconnexion
                </button>
            </form>
            @else
            <a href="{{ route('login') }}" class="btn btn-sm btn-outline w-100"
               style="color:#fff;border-color:rgba(255,255,255,0.3);display:flex;align-items:center;justify-content:center;gap:8px;padding:8px;text-decoration:none;">
                Connexion
            </a>
            @endauth
        </div>
    </aside>

    {{-- ═══════════════════════════════════════════════════ MAIN CONTENT ═══ --}}
    <div class="main-content">

        <header class="topbar">
            <button class="btn-ghost btn btn-icon" id="menuToggle" style="display:none">☰</button>
            <div class="topbar-title">@yield('page-title', 'Tableau de bord')</div>
            <div class="topbar-actions">
                <div class="notification-wrapper" style="position:relative;">
                    <button class="topbar-btn" id="notifBtn" title="Notifications" onclick="toggleNotifications()"
                            style="position:relative;">
                        <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/>
                            <path d="M13.73 21a2 2 0 0 1-3.46 0"/>
                        </svg>
                        <span class="notif-dot" id="notifDot" style="display:none;"></span>
                    </button>

                    <div id="notifDropdown"
                         style="display:none;position:absolute;top:100%;right:0;width:320px;background:#fff;border-radius:8px;box-shadow:0 4px 20px rgba(0,0,0,0.15);z-index:1000;margin-top:8px;max-height:400px;overflow-y:auto;">
                        <div style="padding:12px 16px;border-bottom:1px solid #eee;display:flex;justify-content:space-between;align-items:center;">
                            <span style="font-weight:600;font-size:0.9rem;">Notifications</span>
                            <span id="notifCount" style="background:var(--primary);color:#fff;padding:2px 8px;border-radius:10px;font-size:0.7rem;">0</span>
                        </div>
                        <div id="notifList" style="padding:8px 0;">
                            <div style="padding:20px;text-align:center;color:#888;">Chargement…</div>
                        </div>
                        <div style="padding:8px 16px;border-top:1px solid #eee;text-align:center;">
                            <a href="{{ route('notifications.index') }}"
                               style="font-size:0.8rem;color:var(--primary);text-decoration:none;">
                                Voir toutes les notifications
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <main class="page-content">
            @if(session('success'))
            <div class="alert alert-success">
                <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <polyline points="22 4 12 14.01 9 11.01"/>
                </svg>
                {{ session('success') }}
            </div>
            @endif

            @if(session('warning'))
            <div class="alert alert-warning">⚠️ {{ session('warning') }}</div>
            @endif

            @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
            @endif

            @yield('content')
        </main>
    </div>
</div>

<script>
document.getElementById('menuToggle')?.addEventListener('click', () => {
    document.getElementById('sidebar').classList.toggle('open');
});

function toggleNotifications() {
    const dropdown = document.getElementById('notifDropdown');
    dropdown.style.display = dropdown.style.display === 'none' ? 'block' : 'none';
    if (dropdown.style.display === 'block') loadNotifications();
}

document.addEventListener('click', function (e) {
    const wrapper  = document.querySelector('.notification-wrapper');
    const dropdown = document.getElementById('notifDropdown');
    if (wrapper && !wrapper.contains(e.target)) dropdown.style.display = 'none';
});

function loadNotifications() {
    fetch('{{ route("api.notifications.data") }}')
        .then(r => r.json())
        .then(data => {
            const notifList  = document.getElementById('notifList');
            const notifCount = document.getElementById('notifCount');
            const notifDot   = document.getElementById('notifDot');

            notifCount.textContent = data.totalCount;
            notifDot.style.display = data.totalCount > 0 ? 'block' : 'none';

            let items = [
                ...data.absences.map(i => ({ ...i, icon: 'calendar', color: '#f59e0b' })),
                ...data.news.map(i    => ({ ...i, icon: 'news',     color: '#0ea5e9' })),
            ].sort((a, b) => new Date(b.created_at) - new Date(a.created_at));

            if (!items.length) {
                notifList.innerHTML = '<div style="padding:20px;text-align:center;color:#888;">Aucune notification</div>';
                return;
            }

            notifList.innerHTML = items.slice(0, 10).map(item => `
                <a href="${item.url}"
                   style="display:flex;align-items:center;gap:10px;padding:10px 16px;text-decoration:none;color:inherit;border-bottom:1px solid #f0f0f0;">
                    <div style="width:32px;height:32px;border-radius:50%;background:${item.color};display:flex;align-items:center;justify-content:center;color:#fff;font-size:.8rem;flex-shrink:0;">
                        ${item.type === 'absence' ? '📅' : '📰'}
                    </div>
                    <div style="flex:1;min-width:0;">
                        <div style="font-size:.85rem;font-weight:500;">${item.message}</div>
                        <div style="font-size:.75rem;color:#888;">${item.created_at}</div>
                    </div>
                </a>
            `).join('');
        })
        .catch(() => {
            document.getElementById('notifList').innerHTML =
                '<div style="padding:20px;text-align:center;color:#888;">Erreur de chargement</div>';
        });
}

document.querySelectorAll('[data-count]').forEach(el => {
    const target = parseInt(el.getAttribute('data-count'));
    let current  = 0;
    const step   = Math.ceil(target / 40);
    const timer  = setInterval(() => {
        current = Math.min(current + step, target);
        el.textContent = current.toLocaleString('fr-FR');
        if (current >= target) clearInterval(timer);
    }, 30);
});
</script>

@stack('scripts')
</body>
</html>
