<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'HospitalRH') — HospitalRH</title>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    @stack('styles')
    <style>
        .nav-submenu { padding-left: 20px; margin: 4px 0; }
        .nav-sublink { display: block; padding: 6px 12px; font-size: 0.85rem; color: #888; border-radius: 6px; text-decoration: none; margin: 2px 0; transition: all 0.2s; }
        .nav-sublink:hover { background: rgba(255,255,255,0.1); color: #fff; }
        .nav-sublink.active { background: var(--primary); color: #fff; }
    </style>
</head>
<body>
<div class="app-wrapper">
    <!-- SIDEBAR -->
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <a href="{{ route('dashboard') }}" class="brand">
                <div class="brand-icon">🏥</div>
                <div class="brand-name">
                    HospitalRH
                    <span>Gestion RH</span>
                </div>
            </a>
        </div>

        <nav class="sidebar-nav">
            {{-- Dashboard - Visible to all authenticated users --}}
            <div class="nav-section-label">Principal</div>
            <a href="{{ route('dashboard') }}" class="nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                <svg class="nav-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/>
                    <rect x="14" y="14" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/>
                </svg>
                Tableau de bord
            </a>

            {{-- Profile - Visible to Employee only, not admin/RH --}}
            @if(Auth::user() && Auth::user()->role === 'employee')
            <a href="{{ route('profile') }}" class="nav-item {{ request()->routeIs('profile') ? 'active' : '' }}">
                <svg class="nav-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                    <circle cx="12" cy="7" r="4"/>
                </svg>
                Mon Profil
            </a>
            @endif

            {{-- Personnel section - Visible to Admin and RH only --}}
            @if(Auth::user() && in_array(Auth::user()->role, ['admin', 'rh']))
            <div class="nav-section-label">Personnel</div>
            <a href="{{ route('employees.index') }}" class="nav-item {{ request()->routeIs('employees.*') ? 'active' : '' }}">
                <svg class="nav-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/>
                    <path d="M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/>
                </svg>
                Liste du Personnel
            </a>
            <a href="{{ route('trombinoscope') }}" class="nav-item {{ request()->routeIs('trombinoscope') ? 'active' : '' }}">
                <svg class="nav-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="9" cy="10" r="2"/><circle cx="15" cy="10" r="2"/>
                    <path d="M6 16c0-1.1 1.3-2 3-2s3 .9 3 2M12 16c0-1.1 1.3-2 3-2s3 .9 3 2"/>
                </svg>
                Trombinoscope
            </a>
            <a href="{{ route('news.index') }}" class="nav-item {{ request()->routeIs('news.*') ? 'active' : '' }}">
                <svg class="nav-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/>
                </svg>
                Actualités
            </a>
            @endif

            {{-- Employee can see trombinoscope and their own planning --}}
            @if(Auth::user() && Auth::user()->role === 'employee')
            <div class="nav-section-label">Mon Espace</div>
            <a href="{{ route('trombinoscope') }}" class="nav-item {{ request()->routeIs('trombinoscope') ? 'active' : '' }}">
                <svg class="nav-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="9" cy="10" r="2"/><circle cx="15" cy="10" r="2"/>
                    <path d="M6 16c0-1.1 1.3-2 3-2s3 .9 3 2M12 16c0-1.1 1.3-2 3-2s3 .9 3 2"/>
                </svg>
                Trombinoscope
            </a>
            <a href="{{ route('planning.show', ['employee' => Auth::user()->employee_id ?? 0]) }}" class="nav-item {{ request()->routeIs('planning.show') ? 'active' : '' }}">
                <svg class="nav-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                    <line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/>
                    <line x1="3" y1="10" x2="21" y2="10"/>
                </svg>
                Mon Planning
            </a>
            @endif

            {{-- Temps & Présence - Visible to Admin and RH only --}}
            @if(Auth::user() && in_array(Auth::user()->role, ['admin', 'rh']))
            <div class="nav-section-label">Temps & Présence</div>
            <a href="{{ route('planning.weekly') }}" class="nav-item {{ request()->routeIs('planning.*') ? 'active' : '' }}">
                <svg class="nav-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                    <line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/>
                    <line x1="3" y1="10" x2="21" y2="10"/>
                </svg>
                Planning
            </a>
            <a href="{{ route('temps.vue-ensemble') }}" class="nav-item {{ request()->routeIs('temps.vue-ensemble') ? 'active' : '' }}">
                <svg class="nav-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="10"/>
                    <polyline points="12 6 12 12 16 14"/>
                </svg>
                Vue d'ensemble
            </a>
            @endif

            {{-- Absences & Congés --}}
            <div class="nav-section-label">Absences & Congés</div>
            <a href="{{ route('absences.index') }}" class="nav-item {{ request()->routeIs('absences.index') || request()->routeIs('absences.show') ? 'active' : '' }}">
                <svg class="nav-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/>
                </svg>
                Liste des demandes
            </a>
            
            {{-- State visual of absences - Only for Admin/RH --}}
            @if(Auth::user() && in_array(Auth::user()->role, ['admin', 'rh']))
            <a href="{{ route('absences.calendar') }}" class="nav-item {{ request()->routeIs('absences.calendar') ? 'active' : '' }}">
                <svg class="nav-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                    <line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/>
                    <line x1="3" y1="10" x2="21" y2="10"/>
                </svg>
               Etat visuel des absences
            </a>
            @endif
            
            {{-- Counters - Visible to Admin and RH only --}}
            @if(Auth::user() && in_array(Auth::user()->role, ['admin', 'rh']))
            <a href="{{ route('absences.counters') }}" class="nav-item {{ request()->routeIs('absences.counters') ? 'active' : '' }}">
                <svg class="nav-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/>
                    <line x1="6" y1="20" x2="6" y2="14"/>
                </svg>
                Compteurs et droits d'absences
            </a>
            @endif

            {{-- Salaries - Role based visibility --}}
            @if(Auth::user() && in_array(Auth::user()->role, ['admin', 'rh']))
            <div class="nav-section-label">Paie</div>
            <a href="{{ route('salary.index') }}" class="nav-item {{ request()->routeIs('salary.*') ? 'active' : '' }}">
                <svg class="nav-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>
                </svg>
                Salaires
                <a href="{{ route('salary.index') }}" class="nav-link">
    Paie
</a>
<a href="{{ route('variables.index') }}" class="nav-link">
    Éléments variables
</a>
            </a>
            @endif
            
            {{-- Employee can see their own salary --}}
            @if(Auth::user() && Auth::user()->role === 'employee' && Auth::user()->employee_id)
            <div class="nav-section-label">Paie</div>
            <a href="{{ route('salary.show', Auth::user()->employee_id) }}" class="nav-item {{ request()->routeIs('salary.show') ? 'active' : '' }}">
                <svg class="nav-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>
                </svg>
                Mon Salaire
            </a>
            @endif
        </nav>

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
                <button type="submit" class="btn btn-sm btn-outline w-100" style="color: #fff; border-color: rgba(255,255,255,0.3);">
                    <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/>
                    </svg>
                    Déconnexion
                </button>
            </form>
            @else
            <a href="{{ route('login') }}" class="btn btn-sm btn-outline w-100" style="color: #fff; border-color: rgba(255,255,255,0.3); display: flex; align-items: center; justify-content: center; gap: 8px; padding: 8px;">
                <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/><polyline points="10 17 15 12 10 7"/><line x1="15" y1="12" x2="3" y2="12"/>
                </svg>
                Connexion
            </a>
            @endauth
        </div>
    </aside>

    <!-- MAIN CONTENT -->
    <div class="main-content">
        <header class="topbar">
            <button class="btn-ghost btn btn-icon" id="menuToggle" style="display:none">☰</button>
            <div class="topbar-title">@yield('page-title', 'Tableau de bord')</div>
            <div class="topbar-actions">
                <div class="notification-wrapper" style="position: relative;">
                    <button class="topbar-btn" id="notifBtn" title="Notifications" onclick="toggleNotifications()">
                        <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/>
                        </svg>
                        <span class="notif-dot" id="notifDot" style="display: none;"></span>
                    </button>
                    <div class="notification-dropdown" id="notifDropdown" style="display: none; position: absolute; top: 100%; right: 0; width: 320px; background: white; border-radius: 8px; box-shadow: 0 4px 20px rgba(0,0,0,0.15); z-index: 1000; margin-top: 8px; max-height: 400px; overflow-y: auto;">
                        <div style="padding: 12px 16px; border-bottom: 1px solid #eee; display: flex; justify-content: space-between; align-items: center;">
                            <span style="font-weight: 600; font-size: 0.9rem;">Notifications</span>
                            <span id="notifCount" style="background: var(--primary); color: white; padding: 2px 8px; border-radius: 10px; font-size: 0.7rem;">0</span>
                        </div>
                        <div id="notifList" style="padding: 8px 0;">
                            <div style="padding: 20px; text-align: center; color: #888;">Chargement...</div>
                        </div>
                        <div style="padding: 8px 16px; border-top: 1px solid #eee; text-align: center;">
                            <a href="{{ route('notifications.index') }}" style="font-size: 0.8rem; color: var(--primary); text-decoration: none;">Voir toutes les notifications</a>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <main class="page-content">
            @if(session('success'))
                <div class="alert alert-success">
                    <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/>
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

// Notifications
function toggleNotifications() {
    const dropdown = document.getElementById('notifDropdown');
    dropdown.style.display = dropdown.style.display === 'none' ? 'block' : 'none';
    if (dropdown.style.display === 'block') {
        loadNotifications();
    }
}

// Close dropdown when clicking outside
document.addEventListener('click', function(event) {
    const wrapper = document.querySelector('.notification-wrapper');
    const dropdown = document.getElementById('notifDropdown');
    if (wrapper && !wrapper.contains(event.target)) {
        dropdown.style.display = 'none';
    }
});

function loadNotifications() {
    fetch('{{ route("api.notifications.data") }}')
        .then(response => response.json())
        .then(data => {
            const notifList = document.getElementById('notifList');
            const notifCount = document.getElementById('notifCount');
            const notifDot = document.getElementById('notifDot');
            
            // Update count
            notifCount.textContent = data.totalCount;
            if (data.totalCount > 0) {
                notifDot.style.display = 'block';
            }
            
            // Combine absences and news
            let items = [];
            
            // Add pending absences
            data.absences.forEach(item => {
                items.push({
                    ...item,
                    icon: 'calendar',
                    color: '#f59e0b'
                });
            });
            
            // Add news
            data.news.forEach(item => {
                items.push({
                    ...item,
                    icon: 'news',
                    color: '#0ea5e9'
                });
            });
            
            // Sort by date (newest first)
            items.sort((a, b) => new Date(b.created_at) - new Date(a.created_at));
            
            if (items.length === 0) {
                notifList.innerHTML = '<div style="padding: 20px; text-align: center; color: #888;">Aucune notification</div>';
                return;
            }
            
            notifList.innerHTML = items.slice(0, 10).map(item => `
                <a href="${item.url}" style="display: flex; align-items: center; gap: 10px; padding: 10px 16px; text-decoration: none; color: inherit; border-bottom: 1px solid #f0f0f0; transition: background 0.2s;" onmouseover="this.style.background='#f8f9fa'" onmouseout="this.style.background='transparent'">
                    <div style="width: 32px; height: 32px; border-radius: 50%; background: ${item.color}; display: flex; align-items: center; justify-content: center; color: white; font-size: 0.8rem; flex-shrink: 0;">
                        ${item.type === 'absence' ? '📅' : '📰'}
                    </div>
                    <div style="flex: 1; min-width: 0;">
                        <div style="font-size: 0.85rem; font-weight: 500; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">${item.message}</div>
                        <div style="font-size: 0.75rem; color: #888;">${item.created_at}</div>
                    </div>
                </a>
            `).join('');
        })
        .catch(error => {
            console.error('Error loading notifications:', error);
            document.getElementById('notifList').innerHTML = '<div style="padding: 20px; text-align: center; color: #888;">Erreur de chargement</div>';
        });
}

document.querySelectorAll('[data-count]').forEach(el => {
    const target = parseInt(el.getAttribute('data-count'));
    let current = 0;
    const step = Math.ceil(target / 40);
    const timer = setInterval(() => {
        current = Math.min(current + step, target);
        el.textContent = current.toLocaleString('fr-FR');
        if (current >= target) clearInterval(timer);
    }, 30);
});
</script>
@stack('scripts')
</body>
</html>
