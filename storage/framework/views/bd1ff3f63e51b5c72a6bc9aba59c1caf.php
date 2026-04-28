<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <title><?php echo $__env->yieldContent('title', config('app.name', 'HospitalRH')); ?> — <?php echo e(config('app.name', 'HospitalRH')); ?></title>
    <link rel="stylesheet" href="<?php echo e(asset('css/app.css')); ?>">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <?php echo $__env->yieldPushContent('styles'); ?>
    <style>
        /* ══════════════════════════════════════════
           LAYOUT DE BASE — flex row sans gap
        ══════════════════════════════════════════ */
        .app-wrapper {
            display: flex;
            min-height: 100vh;
            align-items: stretch;
        }

        /* ══════════════════════════════════════════
           SIDEBAR
        ══════════════════════════════════════════ */
        .sidebar {
            width: 260px;
            flex-shrink: 0;
            background: #0d2238;
            display: flex;
            flex-direction: column;
            height: 100vh;
            position: sticky;
            top: 0;
            overflow: visible !important;
            transition: width .25s ease;
            z-index: 100;
        }

        /* La nav prend tout l'espace restant et scroll */
        .sidebar-nav {
            flex: 1 1 0;
            min-height: 0;
            overflow-y: auto;
            overflow-x: hidden;
            padding: 16px 12px;
            scrollbar-width: thin;
            scrollbar-color: rgba(255,255,255,.15) transparent;
        }
        .sidebar-nav::-webkit-scrollbar { width: 4px; }
        .sidebar-nav::-webkit-scrollbar-thumb { background: rgba(255,255,255,.2); border-radius: 4px; }

        /* Footer toujours en bas */
        .sidebar-footer {
            flex-shrink: 0;
        }

        /* ── Bouton collapse ── */
       .sidebar-collapse-btn {
    position: absolute;
    top: 50%;
    right: -14px;
    transform: translateY(-50%);
    width: 28px;
    height: 28px;
    background: #14b8a6;
    border: none;
    border-radius: 50%;
    color: #fff;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    z-index: 200;
    box-shadow: 0 2px 8px rgba(0,0,0,.25);
    transition: background .2s;
    font-size: 10px;
    line-height: 1;
    overflow: visible;
}
        .sidebar-collapse-btn:hover { background: #0d9488; }

        /* ── État collapsed ── */
        .sidebar.collapsed { width: 64px; }

        .sidebar.collapsed .nav-section-label,
        .sidebar.collapsed .brand-name,
        .sidebar.collapsed .nav-item > span,
        .sidebar.collapsed .nav-item > .nav-label,
        .sidebar.collapsed .nav-badge-live,
        .sidebar.collapsed .user-info,
        .sidebar.collapsed .sidebar-footer .btn span,
        .sidebar.collapsed .sidebar-footer form .btn span {
            display: none !important;
        }

        .sidebar.collapsed .nav-item {
            justify-content: center !important;
            padding-left: 0 !important;
            padding-right: 0 !important;
        }
        .sidebar.collapsed .nav-icon { margin: 0 !important; }
        .sidebar.collapsed .sidebar-header a { justify-content: center; }
        .sidebar.collapsed .user-card { justify-content: center; }
        .sidebar.collapsed .user-avatar { margin: 0 auto; }

        /* ══════════════════════════════════════════
           MAIN CONTENT — prend le reste de la largeur
        ══════════════════════════════════════════ */
        .main-content {
            flex: 1;
            min-width: 0;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        /* ── Styles nav ── */
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

    
    <aside class="sidebar" id="sidebar">

        
        <button class="sidebar-collapse-btn" id="collapseBtn" onclick="toggleSidebar()" title="Réduire">
            <i class="fas fa-chevron-left" id="collapseIcon"></i>
        </button>

        <div class="sidebar-header">
            <?php
                $tenant     = auth()->user()?->tenant;
                $brandColor = $tenant?->brand_color ?? '#14b8a6';
                $appName    = $tenant?->name ?? config('app.name', 'HospitalRH');
                $appSub     = 'Gestion RH';
                $logoPath   = $tenant?->logo_path;
                $initials   = $tenant?->initials ?? strtoupper(substr($appName, 0, 1));

                $dashboardHref = route('admin.dashboard');
                if (auth()->check()) {
                    $role = auth()->user()->role;
                    if ($role === 'superadmin')                    $dashboardHref = route('superadmin.dashboard');
                    elseif (in_array($role, ['admin', 'rh']))     $dashboardHref = route('admin.dashboard');
                    else                                           $dashboardHref = route('employee.dashboard');
                }
            ?>

            <a href="<?php echo e($dashboardHref); ?>" style="display:flex;align-items:center;gap:10px;text-decoration:none;">
                <?php if($logoPath): ?>
                    <img src="<?php echo e(asset('storage/' . $logoPath)); ?>" alt="logo"
                         style="width:36px;height:36px;object-fit:contain;border-radius:8px;flex-shrink:0;">
                <?php else: ?>
                    <div class="brand-icon-custom" style="background:<?php echo e($brandColor); ?>;">
                        <?php echo e($initials); ?>

                    </div>
                <?php endif; ?>
                <div class="brand-name">
                    <?php echo e($appName); ?>

                    <span><?php echo e($appSub); ?></span>
                </div>
            </a>
        </div>

        <nav class="sidebar-nav">

            
            <?php if(Auth::check() && Auth::user()->role === 'superadmin'): ?>
            <a href="<?php echo e(route('superadmin.dashboard')); ?>"
               class="nav-item <?php echo e(request()->routeIs('superadmin.dashboard') ? 'active' : ''); ?>">
                <svg class="nav-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25"/>
                </svg>
                <span>Dashboard SuperAdmin</span>
            </a>
            <?php endif; ?>

            
            <div class="nav-section-label">Principal</div>

            <a href="<?php echo e($dashboardHref); ?>"
               class="nav-item <?php echo e(request()->routeIs(['admin.dashboard', 'employee.dashboard', 'superadmin.dashboard']) ? 'active' : ''); ?>">
                <svg class="nav-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <rect x="3" y="3" width="7" height="7" rx="1"/>
                    <rect x="14" y="3" width="7" height="7" rx="1"/>
                    <rect x="14" y="14" width="7" height="7" rx="1"/>
                    <rect x="3" y="14" width="7" height="7" rx="1"/>
                </svg>
                <span>Tableau de bord</span>
            </a>

            
            <?php if(Auth::check() && Auth::user()->role === 'employee'): ?>
            <a href="<?php echo e(route('profile')); ?>" class="nav-item <?php echo e(request()->routeIs('profile') ? 'active' : ''); ?>">
                <svg class="nav-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                    <circle cx="12" cy="7" r="4"/>
                </svg>
                <span>Mon Profil</span>
            </a>
            <?php endif; ?>

            
            <?php if(Auth::check() && in_array(Auth::user()->role, ['admin', 'rh'])): ?>
            <div class="nav-section-label">Personnel</div>

            <a href="<?php echo e(route('employees.index')); ?>"
               class="nav-item <?php echo e(request()->routeIs('employees.*') ? 'active' : ''); ?>">
                <svg class="nav-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                    <circle cx="9" cy="7" r="4"/>
                    <path d="M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/>
                </svg>
                <span>Liste du Personnel</span>
            </a>

            <a href="<?php echo e(route('trombinoscope')); ?>"
               class="nav-item <?php echo e(request()->routeIs('trombinoscope') ? 'active' : ''); ?>">
                <svg class="nav-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <rect x="3" y="3" width="18" height="18" rx="2"/>
                    <circle cx="9" cy="10" r="2"/><circle cx="15" cy="10" r="2"/>
                    <path d="M6 16c0-1.1 1.3-2 3-2s3 .9 3 2M12 16c0-1.1 1.3-2 3-2s3 .9 3 2"/>
                </svg>
                <span>Trombinoscope</span>
            </a>

            <a href="<?php echo e(route('news.index')); ?>"
               class="nav-item <?php echo e(request()->routeIs('news.*') ? 'active' : ''); ?>">
                <svg class="nav-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/>
                    <path d="M13.73 21a2 2 0 0 1-3.46 0"/>
                </svg>
                <span>Actualités</span>
            </a>
            <?php endif; ?>

            
            <?php if(Auth::check() && Auth::user()->role === 'employee'): ?>
            <div class="nav-section-label">Mon Espace</div>

            <a href="<?php echo e(route('trombinoscope')); ?>"
               class="nav-item <?php echo e(request()->routeIs('trombinoscope') ? 'active' : ''); ?>">
                <svg class="nav-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <rect x="3" y="3" width="18" height="18" rx="2"/>
                    <circle cx="9" cy="10" r="2"/><circle cx="15" cy="10" r="2"/>
                    <path d="M6 16c0-1.1 1.3-2 3-2s3 .9 3 2M12 16c0-1.1 1.3-2 3-2s3 .9 3 2"/>
                </svg>
                <span>Trombinoscope</span>
            </a>

            <a href="<?php echo e(route('planning.show', ['employee' => Auth::user()->employee_id ?? 0])); ?>"
               class="nav-item <?php echo e(request()->routeIs('planning.show') ? 'active' : ''); ?>">
                <svg class="nav-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                    <line x1="16" y1="2" x2="16" y2="6"/>
                    <line x1="8" y1="2" x2="8" y2="6"/>
                    <line x1="3" y1="10" x2="21" y2="10"/>
                </svg>
                <span>Mon Planning</span>
            </a>
            <?php endif; ?>

            
            <?php if(Auth::check() && in_array(Auth::user()->role, ['admin', 'rh'])): ?>
            <div class="nav-section-label">Temps & Présence</div>

            <a href="<?php echo e(route('planning.weekly')); ?>"
               class="nav-item <?php echo e(request()->routeIs('planning.*') ? 'active' : ''); ?>">
                <svg class="nav-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                    <line x1="16" y1="2" x2="16" y2="6"/>
                    <line x1="8" y1="2" x2="8" y2="6"/>
                    <line x1="3" y1="10" x2="21" y2="10"/>
                </svg>
                <span>Planning</span>
            </a>

            <a href="<?php echo e(route('temps.vue-ensemble')); ?>"
               class="nav-item <?php echo e(request()->routeIs('temps.vue-ensemble') ? 'active' : ''); ?>">
                <svg class="nav-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="10"/>
                    <polyline points="12 6 12 12 16 14"/>
                </svg>
                <span>Vue d'ensemble</span>
            </a>

            <?php
                $pointageEnAttente = 0;
                try {
                    $pointageEnAttente = \App\Models\Pointage::forDate(today()->toDateString())
                        ->where('valide', false)
                        ->where('statut', 'present')
                        ->count();
                } catch (\Exception $e) {}
            ?>

            <a href="<?php echo e(route('pointage.index')); ?>"
               class="nav-item <?php echo e(request()->routeIs('pointage.*') ? 'active' : ''); ?>">
                <svg class="nav-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <rect x="3" y="3" width="8" height="18" rx="1.5"/>
                    <rect x="13" y="3" width="8" height="8" rx="1.5"/>
                    <rect x="13" y="13" width="8" height="8" rx="1.5"/>
                </svg>
                <span>Pointage</span>
                <?php if($pointageEnAttente > 0): ?>
                <span class="nav-badge-live"><?php echo e($pointageEnAttente); ?></span>
                <?php endif; ?>
            </a>
            <?php endif; ?>

            
            <div class="nav-section-label">Absences & Congés</div>

            <a href="<?php echo e(route('absences.index')); ?>"
               class="nav-item <?php echo e(request()->routeIs('absences.index') || request()->routeIs('absences.show') ? 'active' : ''); ?>">
                <svg class="nav-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path d="M9 11l3 3L22 4"/>
                    <path d="M21 12v7a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2h11"/>
                </svg>
                <span>Liste des demandes</span>
            </a>

            <?php if(Auth::check() && in_array(Auth::user()->role, ['admin', 'rh'])): ?>
            <a href="<?php echo e(route('absences.calendar')); ?>"
               class="nav-item <?php echo e(request()->routeIs('absences.calendar') ? 'active' : ''); ?>">
                <svg class="nav-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                    <line x1="16" y1="2" x2="16" y2="6"/>
                    <line x1="8" y1="2" x2="8" y2="6"/>
                    <line x1="3" y1="10" x2="21" y2="10"/>
                </svg>
                <span>État visuel des absences</span>
            </a>

            <a href="<?php echo e(route('absences.counters')); ?>"
               class="nav-item <?php echo e(request()->routeIs('absences.counters') ? 'active' : ''); ?>">
                <svg class="nav-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <line x1="18" y1="20" x2="18" y2="10"/>
                    <line x1="12" y1="20" x2="12" y2="4"/>
                    <line x1="6"  y1="20" x2="6"  y2="14"/>
                </svg>
                <span>Compteurs et droits d'absences</span>
            </a>
            <?php endif; ?>

            
            <?php if(Auth::check() && in_array(Auth::user()->role, ['admin', 'rh'])): ?>
            <div class="nav-section-label">Paie</div>

            <a href="<?php echo e(route('salary.index')); ?>"
               class="nav-item <?php echo e(request()->routeIs('salary.index') || request()->routeIs('salary.show') || request()->routeIs('salary.create') ? 'active' : ''); ?>">
                <svg class="nav-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <line x1="12" y1="1" x2="12" y2="23"/>
                    <path d="M17 5H9.5a3.5 3.5 0 00 0 7h5a3.5 3.5 0 01 0 7H6"/>
                </svg>
                <span>Salaires</span>
            </a>
            <?php endif; ?>

            
            <?php if(Auth::check() && Auth::user()->role === 'employee' && Auth::user()->employee_id): ?>
            <div class="nav-section-label">Paie</div>

            <a href="<?php echo e(route('salary.show', Auth::user()->employee_id)); ?>"
               class="nav-item <?php echo e(request()->routeIs('salary.show') ? 'active' : ''); ?>">
                <svg class="nav-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <line x1="12" y1="1" x2="12" y2="23"/>
                    <path d="M17 5H9.5a3.5 3.5 0 00 0 7h5a3.5 3.5 0 01 0 7H6"/>
                </svg>
                <span>Mon Salaire</span>
            </a>
            <?php endif; ?>

            
            <?php if(Auth::check() && in_array(Auth::user()->role, ['admin', 'rh'])): ?>
            <div class="nav-section-label">GED</div>

            <a href="<?php echo e(route('ged.index')); ?>"
               class="nav-item <?php echo e(request()->routeIs('ged.index') ? 'active' : ''); ?>">
                <svg class="nav-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586
                        a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19
                        a2 2 0 01-2 2z"/>
                </svg>
                <span>Documents</span>
            </a>

            <a href="<?php echo e(route('ged.modeles.index')); ?>"
               class="nav-item <?php echo e(request()->routeIs('ged.modeles.*') ? 'active' : ''); ?>">
                <svg class="nav-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <rect x="3" y="3" width="8" height="8" rx="1.5"/>
                    <rect x="13" y="3" width="8" height="8" rx="1.5"/>
                    <rect x="3" y="13" width="8" height="8" rx="1.5"/>
                    <rect x="13" y="13" width="8" height="8" rx="1.5"/>
                </svg>
                <span>Modèles</span>
            </a>
            <?php endif; ?>

        </nav>
        <a href="<?php echo e(route('ged.entete.index')); ?>"
               class="nav-item <?php echo e(request()->routeIs('ged.entete.*') ? 'active' : ''); ?>">
                <svg class="nav-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <rect x="4" y="4" width="16" height="16" rx="2"/>
                <line x1="4" y1="9" x2="20" y2="9"/>
                <line x1="8" y1="13" x2="16" y2="13"/>
                <line x1="8" y1="17" x2="14" y2="17"/>
                </svg>
                <span>Entête</span>
            </a>

        
        <div class="sidebar-footer">
            <?php if(auth()->guard()->check()): ?>
            <div class="user-card">
                <div class="user-avatar"><?php echo e(substr(Auth::user()->name, 0, 1)); ?></div>
                <div class="user-info">
                    <div class="user-name"><?php echo e(Auth::user()->name); ?></div>
                    <div class="user-role"><?php echo e(Auth::user()->getRoleDisplayName()); ?></div>
                </div>
            </div>
            <form method="POST" action="<?php echo e(route('logout')); ?>">
                <?php echo csrf_field(); ?>
                <button type="submit" class="btn btn-sm btn-outline w-100">
                    <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
                        <polyline points="16 17 21 12 16 7"/>
                        <line x1="21" y1="12" x2="9" y2="12"/>
                    </svg>
                    <span>Déconnexion</span>
                </button>
            </form>
            <?php else: ?>
            <a href="<?php echo e(route('login')); ?>" class="btn btn-sm btn-outline w-100">Connexion</a>
            <?php endif; ?>
        </div>

    </aside>

    
    <div class="main-content">

        <header class="topbar">
            <button class="btn-ghost btn btn-icon" id="menuToggle" style="display:none">
                <i class="fa-solid fa-bars" aria-hidden="true"></i>
            </button>
            <div class="topbar-title"><?php echo $__env->yieldContent('page-title', 'Tableau de bord'); ?></div>
            <div class="topbar-actions">

                
                <div class="notification-wrapper" style="position:relative;">
                    <button class="topbar-btn" id="notifBtn" title="Notifications" onclick="toggleNotifications()" style="position:relative;">
                        <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/>
                            <path d="M13.73 21a2 2 0 0 1-3.46 0"/>
                        </svg>
                        <span class="notif-dot" id="notifDot" style="display:none;"></span>
                    </button>
                    <div id="notifDropdown" style="display:none;position:absolute;top:100%;right:0;width:320px;background:#fff;border-radius:8px;box-shadow:0 4px 20px rgba(0,0,0,0.15);z-index:1000;margin-top:8px;max-height:400px;overflow-y:auto;">
                        <div style="padding:12px 16px;border-bottom:1px solid #eee;display:flex;justify-content:space-between;align-items:center;">
                            <span style="font-weight:600;font-size:0.9rem;">Notifications</span>
                            <span id="notifCount" style="background:var(--primary);color:#fff;padding:2px 8px;border-radius:10px;font-size:0.7rem;">0</span>
                        </div>
                        <div id="notifList" style="padding:8px 0;">
                            <div style="padding:20px;text-align:center;color:#888;">Chargement…</div>
                        </div>
                        <div style="padding:8px 16px;border-top:1px solid #eee;text-align:center;">
                            <a href="<?php echo e(route('notifications.index')); ?>" style="font-size:0.8rem;color:var(--primary);text-decoration:none;">
                                Voir toutes les notifications
                            </a>
                        </div>
                    </div>
                </div>

                
                <div class="export-wrapper" style="position:relative;">
                    <button class="topbar-btn" id="exportBtn" title="Exports" onclick="toggleExportDropdown()">
                        <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path d="M12 10l-5.5 5.5h11L12 10z"/>
                        </svg>
                    </button>
                    <div class="export-dropdown" id="exportDropdown" style="display:none;position:absolute;top:100%;right:0;width:280px;background:white;border-radius:8px;box-shadow:0 4px 20px rgba(0,0,0,0.15);z-index:1000;margin-top:8px;max-height:400px;overflow-y:auto;">
                        <div style="padding:12px 16px;border-bottom:1px solid #eee;font-weight:600;color:var(--primary);">
                            <i class="fa-solid fa-chart-column" aria-hidden="true"></i> Exports
                        </div>
                        <a href="<?php echo e(route('employees.export-pdf')); ?>" style="display:block;padding:12px 16px;text-decoration:none;color:inherit;border-bottom:1px solid #f0f0f0;transition:background .2s;" onmouseover="this.style.background='#f8f9fa'" onmouseout="this.style.background='transparent'">Liste Personnel (PDF)</a>
                        <a href="<?php echo e(route('trombinoscope')); ?>" style="display:block;padding:12px 16px;text-decoration:none;color:inherit;border-bottom:1px solid #f0f0f0;transition:background .2s;" onmouseover="this.style.background='#f8f9fa'" onmouseout="this.style.background='transparent'">Trombinoscope</a>
                        <a href="/salary/export" style="display:block;padding:12px 16px;text-decoration:none;color:inherit;border-bottom:1px solid #f0f0f0;transition:background .2s;" onmouseover="this.style.background='#f8f9fa'" onmouseout="this.style.background='transparent'">Bulletins Paie</a>
                        <a href="<?php echo e(route('absences.counters')); ?>" style="display:block;padding:12px 16px;text-decoration:none;color:inherit;border-bottom:1px solid #f0f0f0;transition:background .2s;" onmouseover="this.style.background='#f8f9fa'" onmouseout="this.style.background='transparent'">Compteurs Absences</a>
                        <a href="<?php echo e(route('planning.monthly')); ?>" style="display:block;padding:12px 16px;text-decoration:none;color:inherit;border-bottom:1px solid #f0f0f0;transition:background .2s;" onmouseover="this.style.background='#f8f9fa'" onmouseout="this.style.background='transparent'">Planning Mensuel</a>
                        <a href="<?php echo e(route('planning.weekly')); ?>" style="display:block;padding:12px 16px;text-decoration:none;color:inherit;transition:background .2s;" onmouseover="this.style.background='#f8f9fa'" onmouseout="this.style.background='transparent'">Planning Hebdo</a>
                    </div>
                </div>

            </div>
        </header>

        <main class="page-content">
            <?php if(session('success')): ?>
            <div class="alert alert-success">
                <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <polyline points="22 4 12 14.01 9 11.01"/>
                </svg>
                <?php echo e(session('success')); ?>

            </div>
            <?php endif; ?>

            <?php if(session('warning')): ?>
            <div class="alert alert-warning">⚠️ <?php echo e(session('warning')); ?></div>
            <?php endif; ?>

            <?php if(session('error')): ?>
            <div class="alert alert-danger"><?php echo e(session('error')); ?></div>
            <?php endif; ?>

            <?php echo $__env->yieldContent('content'); ?>
            <?php echo $__env->make('components.chatbot', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

            
            <div id="chatPopup" class="whatsapp-chat" style="display:none;">
                <div class="whatsapp-header">
                    <div class="whatsapp-avatar"><i class="fa-solid fa-robot" aria-hidden="true"></i></div>
                    <div>
                        <div class="whatsapp-title">Assistant RH</div>
                        <div class="whatsapp-subtitle">HospitalRH · OpenAI</div>
                    </div>
                    <button onclick="toggleChatPopup()" class="whatsapp-close" title="Minimiser">−</button>
                </div>
                <div class="whatsapp-suggestions">
                    <button onclick="sendSuggestion('Stats RH globales')">Stats</button>
                    <button onclick="sendSuggestion('Salaire matricule 1')">Salaire</button>
                    <button onclick="sendSuggestion('Planning matricule 1 avril')">Planning</button>
                </div>
                <div class="whatsapp-messages" id="whatsappMessages">
                    <div class="whatsapp-message whatsapp-bot">
                        Bonjour ! Assistant RH prêt. Demandez stats, salaires, plannings...
                    </div>
                </div>
                <div class="whatsapp-input-area">
                    <textarea id="whatsappInput" placeholder="Tapez votre question..." rows="1" onkeydown="handleKey(event)" oninput="autoResize(this)"></textarea>
                    <button id="whatsappSend" onclick="sendWhatsAppMessage()">
                        <i class="fa-solid fa-paper-plane" aria-hidden="true"></i>
                    </button>
                </div>
            </div>

        </main>
    </div>
</div>

<style>
.floating-chat-btn {
    position: fixed; bottom: 24px; right: 24px;
    width: 60px; height: 60px;
    background: linear-gradient(135deg, #1a1a2e 0%, #c8102e 100%);
    border-radius: 50%;
    display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 4px;
    box-shadow: 0 8px 32px rgba(26,26,46,0.4);
    border: 2px solid rgba(255,255,255,0.2);
    z-index: 10000;
    transition: all 0.3s cubic-bezier(0.4,0,0.2,1);
    text-decoration: none; color: white;
}
.floating-chat-btn:hover { transform: scale(1.1); box-shadow: 0 16px 48px rgba(26,26,46,0.6); }
.floating-chat-btn.active { box-shadow: 0 8px 32px rgba(200,16,46,0.4); }
.chat-icon { font-size: 24px; animation: pulse-chat 2s infinite; }
.chat-badge { background: rgba(255,255,255,0.9); color: #1a1a2e; font-size: 9px; font-weight: 700; padding: 2px 6px; border-radius: 10px; transform: scale(0.8); }
@keyframes pulse-chat { 0%,100% { transform: scale(1); } 50% { transform: scale(1.1); } }

/* Responsive */
@media (max-width: 768px) {
    .sidebar { position: fixed; top: 0; left: 0; bottom: 0; transform: translateX(-100%); transition: transform .3s ease, width .25s ease; }
    .sidebar.open { transform: translateX(0); }
    .main-content { margin-left: 0 !important; }
    #menuToggle { display: flex !important; }
    .sidebar-collapse-btn { display: none; }
}
</style>

<script>
// ── Sidebar collapse ─────────────────────────────────────────────
function toggleSidebar() {
    const sidebar     = document.getElementById('sidebar');
    const icon        = document.getElementById('collapseIcon');
    sidebar.classList.toggle('collapsed');
    const isCollapsed = sidebar.classList.contains('collapsed');
    icon.style.transform = isCollapsed ? 'rotate(180deg)' : 'rotate(0deg)';
    localStorage.setItem('sidebarCollapsed', isCollapsed);
}

// Restaurer l'état au chargement
document.addEventListener('DOMContentLoaded', () => {
    if (localStorage.getItem('sidebarCollapsed') === 'true') {
        document.getElementById('sidebar').classList.add('collapsed');
        document.getElementById('collapseIcon').style.transform = 'rotate(180deg)';
    }
});

// ── Chat popup ───────────────────────────────────────────────────
function toggleChatPopup() {
    const popup = document.getElementById('chatPopup');
    const btn   = document.querySelector('.floating-chat-btn');
    popup.style.display = popup.style.display === 'none' ? 'block' : 'none';
    if (btn) btn.classList.toggle('active');
}

let threadId = localStorage.getItem('chatThread') || null;

function autoResize(el) {
    el.style.height = 'auto';
    el.style.height = el.scrollHeight + 'px';
}

function handleKey(e) {
    if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); sendWhatsAppMessage(); }
}

function sendSuggestion(text) {
    document.getElementById('whatsappInput').value = text;
    sendWhatsAppMessage();
}

async function sendWhatsAppMessage() {
    const input    = document.getElementById('whatsappInput');
    const btn      = document.getElementById('whatsappSend');
    const messages = document.getElementById('whatsappMessages');
    const text     = input.value.trim();
    if (!text) return;

    const userMsg = document.createElement('div');
    userMsg.className   = 'whatsapp-message whatsapp-user';
    userMsg.textContent = text;
    messages.appendChild(userMsg);
    messages.scrollTop  = messages.scrollHeight;

    input.value = ''; autoResize(input); btn.disabled = true;

    const typing = document.createElement('div');
    typing.className = 'whatsapp-message whatsapp-bot typing';
    typing.innerHTML = '<span class="typing-dot"></span><span class="typing-dot"></span><span class="typing-dot"></span>';
    messages.appendChild(typing);
    messages.scrollTop = messages.scrollHeight;

    try {
        const res  = await fetch('/chat', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
            body: JSON.stringify({ message: text, thread: threadId }),
        });
        const data = await res.json();
        typing.remove();
        if (data.reply) {
            threadId = data.thread;
            localStorage.setItem('chatThread', threadId);
            const botMsg = document.createElement('div');
            botMsg.className = 'whatsapp-message whatsapp-bot';
            botMsg.innerHTML = data.reply.replace(/\n/g, '<br>');
            messages.appendChild(botMsg);
        } else {
            const err = document.createElement('div');
            err.className = 'whatsapp-message whatsapp-bot';
            err.textContent = 'Erreur serveur';
            messages.appendChild(err);
        }
    } catch {
        typing.remove();
        const err = document.createElement('div');
        err.className = 'whatsapp-message whatsapp-bot';
        err.textContent = 'Pas de connexion';
        messages.appendChild(err);
    } finally {
        btn.disabled = false; input.focus();
        messages.scrollTop = messages.scrollHeight;
    }
}

// ── Menu mobile ──────────────────────────────────────────────────
document.getElementById('menuToggle')?.addEventListener('click', () => {
    document.getElementById('sidebar').classList.toggle('open');
});

document.addEventListener('keydown', (e) => { if (e.key === 'Escape') toggleChatPopup(); });

// ── Export Dropdown ──────────────────────────────────────────────
function toggleExportDropdown() {
    const d = document.getElementById('exportDropdown');
    d.style.display = d.style.display === 'none' ? 'block' : 'none';
}
document.addEventListener('click', function(e) {
    const wrapper = document.querySelector('.export-wrapper');
    if (wrapper && !wrapper.contains(e.target))
        document.getElementById('exportDropdown').style.display = 'none';
});

// ── Notifications ────────────────────────────────────────────────
function toggleNotifications() {
    const d = document.getElementById('notifDropdown');
    d.style.display = d.style.display === 'none' ? 'block' : 'none';
    if (d.style.display === 'block') loadNotifications();
}
document.addEventListener('click', function(e) {
    const wrapper = document.querySelector('.notification-wrapper');
    if (wrapper && !wrapper.contains(e.target))
        document.getElementById('notifDropdown').style.display = 'none';
});

function loadNotifications() {
    fetch('<?php echo e(route("api.notifications.data")); ?>')
        .then(r => r.json())
        .then(data => {
            const notifList  = document.getElementById('notifList');
            const notifCount = document.getElementById('notifCount');
            const notifDot   = document.getElementById('notifDot');
            notifCount.textContent = data.totalCount;
            notifDot.style.display = data.totalCount > 0 ? 'block' : 'none';
            let items = [
                ...data.absences.map(i => ({ ...i, color: '#f59e0b' })),
                ...data.news.map(i    => ({ ...i, color: '#0ea5e9' })),
            ].sort((a, b) => new Date(b.created_at) - new Date(a.created_at));
            if (!items.length) {
                notifList.innerHTML = '<div style="padding:20px;text-align:center;color:#888;">Aucune notification</div>';
                return;
            }
            notifList.innerHTML = items.slice(0, 10).map(item => `
                <a href="${item.url}" style="display:flex;align-items:center;gap:10px;padding:10px 16px;text-decoration:none;color:inherit;border-bottom:1px solid #f0f0f0;transition:background .2s;" onmouseover="this.style.background='#f8f9fa'" onmouseout="this.style.background='transparent'">
                    <div style="width:32px;height:32px;border-radius:50%;background:${item.color};display:flex;align-items:center;justify-content:center;color:white;font-size:.8rem;flex-shrink:0;">
                        ${item.type === 'absence' ? '<i class="fa-solid fa-calendar-days"></i>' : '<i class="fa-solid fa-newspaper"></i>'}
                    </div>
                    <div style="flex:1;min-width:0;">
                        <div style="font-size:.85rem;font-weight:500;">${item.message}</div>
                        <div style="font-size:.75rem;color:#888;">${item.created_at}</div>
                    </div>
                </a>
            `).join('');
        })
        .catch(() => {
            document.getElementById('notifList').innerHTML = '<div style="padding:20px;text-align:center;color:#888;">Erreur de chargement</div>';
        });
}

// ── Compteurs animés ─────────────────────────────────────────────
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

<?php echo $__env->yieldPushContent('scripts'); ?>
</body>
</html>
<?php /**PATH C:\Users\HP\SIRH-\resources\views/layouts/app.blade.php ENDPATH**/ ?>