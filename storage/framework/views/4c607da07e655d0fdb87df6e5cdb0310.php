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
        <div class="sidebar-header">
            <?php
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
            ?>

            
            <a href="<?php echo e($dashboardHref); ?>" style="display:flex;align-items:center;gap:10px;text-decoration:none;">
                <?php if($logoPath): ?>
                    <img src="<?php echo e(Storage::url($logoPath)); ?>" alt="logo"
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
                Dashboard SuperAdmin
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
                Tableau de bord
            </a>

            
<?php if(Auth::check() && Auth::user()->role === 'employee'): ?>
            <a href="<?php echo e(route('profile')); ?>" class="nav-item <?php echo e(request()->routeIs('profile') ? 'active' : ''); ?>">
                <svg class="nav-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                    <circle cx="12" cy="7" r="4"/>
                </svg>
                Mon Profil
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
                Liste du Personnel
            </a>

            <a href="<?php echo e(route('trombinoscope')); ?>"
               class="nav-item <?php echo e(request()->routeIs('trombinoscope') ? 'active' : ''); ?>">
                <svg class="nav-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <rect x="3" y="3" width="18" height="18" rx="2"/>
                    <circle cx="9" cy="10" r="2"/><circle cx="15" cy="10" r="2"/>
                    <path d="M6 16c0-1.1 1.3-2 3-2s3 .9 3 2M12 16c0-1.1 1.3-2 3-2s3 .9 3 2"/>
                </svg>
                Trombinoscope
            </a>

            <a href="<?php echo e(route('news.index')); ?>"
               class="nav-item <?php echo e(request()->routeIs('news.*') ? 'active' : ''); ?>">
                <svg class="nav-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/>
                    <path d="M13.73 21a2 2 0 0 1-3.46 0"/>
                </svg>
                Actualités
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
                Trombinoscope
            </a>

            <a href="<?php echo e(route('planning.show', ['employee' => Auth::user()->employee_id ?? 0])); ?>"
               class="nav-item <?php echo e(request()->routeIs('planning.show') ? 'active' : ''); ?>">
                <svg class="nav-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                    <line x1="16" y1="2" x2="16" y2="6"/>
                    <line x1="8" y1="2" x2="8" y2="6"/>
                    <line x1="3" y1="10" x2="21" y2="10"/>
                </svg>
                Mon Planning
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
                Planning
            </a>

            <a href="<?php echo e(route('temps.vue-ensemble')); ?>"
               class="nav-item <?php echo e(request()->routeIs('temps.vue-ensemble') ? 'active' : ''); ?>">
                <svg class="nav-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="10"/>
                    <polyline points="12 6 12 12 16 14"/>
                </svg>
                Vue d'ensemble
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
               class="nav-item <?php echo e(request()->routeIs('pointage.*') ? 'active' : ''); ?>"
               style="display:flex;align-items:center;gap:10px;">
                <svg class="nav-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <rect x="3" y="3" width="8" height="18" rx="1.5"/>
                    <rect x="13" y="3" width="8" height="8" rx="1.5"/>
                    <rect x="13" y="13" width="8" height="8" rx="1.5"/>
                </svg>
                Pointage
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
                Liste des demandes
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
                État visuel des absences
            </a>

            <a href="<?php echo e(route('absences.counters')); ?>"
               class="nav-item <?php echo e(request()->routeIs('absences.counters') ? 'active' : ''); ?>">
                <svg class="nav-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <line x1="18" y1="20" x2="18" y2="10"/>
                    <line x1="12" y1="20" x2="12" y2="4"/>
                    <line x1="6"  y1="20" x2="6"  y2="14"/>
                </svg>
                Compteurs et droits d'absences
            </a>
            <?php endif; ?>
<?php if(auth()->guard()->check()): ?>
<?php if(in_array(auth()->user()->role, ['admin', 'rh'])): ?>

    <?php
        $pointageEnAttente = 0;
        try {
            $pointageEnAttente = \App\Models\Pointage::forDate(today()->toDateString())
                ->where('valide', false)
                ->where('statut', 'present')
                ->count();
        } catch (\Exception $e) {}
    ?>

<?php endif; ?>
<?php endif; ?>


            
            <?php if(Auth::check() && in_array(Auth::user()->role, ['admin', 'rh'])): ?>
            <div class="nav-section-label">Paie</div>

            <a href="<?php echo e(route('salary.index')); ?>"
               class="nav-item <?php echo e(request()->routeIs('salary.index') || request()->routeIs('salary.show') || request()->routeIs('salary.create') ? 'active' : ''); ?>">
                <svg class="nav-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <line x1="12" y1="1" x2="12" y2="23"/>
                    <path d="M17 5H9.5a3.5 3.5 0 00 0 7h5a3.5 3.5 0 01 0 7H6"/>
                </svg>
                Salaires
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
                Mon Salaire
            </a>
            <?php endif; ?>

        </nav>

        
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
                    Déconnexion
                </button>
            </form>
            <?php else: ?>
            <a href="<?php echo e(route('login')); ?>" class="btn btn-sm btn-outline w-100">
                Connexion
            </a>
            <?php endif; ?>
        </div>

    </aside>

    
    <div class="main-content">

        <header class="topbar">
            <button class="btn-ghost btn btn-icon" id="menuToggle" style="display:none"><i class="fa-solid fa-bars" aria-hidden="true"></i></button>
            <div class="topbar-title"><?php echo $__env->yieldContent('page-title', 'Tableau de bord'); ?></div>
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
                            <a href="<?php echo e(route('notifications.index')); ?>"
                               style="font-size:0.8rem;color:var(--primary);text-decoration:none;">
                                Voir toutes les notifications
                            </a>
                        </div>
                    </div>
                </div>
                <!-- Global Excel Export Dropdown -->
                <div class="export-wrapper" style="position: relative;">
                    <button class="topbar-btn" id="exportBtn" title="Fichier Excel Imprimable" onclick="toggleExportDropdown()">
                        <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path d="M12 10l-5.5 5.5h11L12 10z"/>
                        </svg>
                    </button>
                    <div class="export-dropdown" id="exportDropdown" style="display: none; position: absolute; top: 100%; right: 0; width: 280px; background: white; border-radius: 8px; box-shadow: 0 4px 20px rgba(0,0,0,0.15); z-index: 1000; margin-top: 8px; max-height: 400px; overflow-y: auto;">
                        <div style="padding: 12px 16px; border-bottom: 1px solid #eee; font-weight: 600; color: var(--primary);"><i class="fa-solid fa-chart-column" aria-hidden="true"></i> Exports</div>
                        <a href="<?php echo e(route('employees.export-pdf')); ?>" style="display: block; padding: 12px 16px; text-decoration: none; color: inherit; border-bottom: 1px solid #f0f0f0; transition: background 0.2s;" onmouseover="this.style.background='#f8f9fa'" onmouseout="this.style.background='transparent'">
                              Liste Personnel (PDF)
                        </a>
                        <a href="<?php echo e(route('trombinoscope')); ?>" style="display: block; padding: 12px 16px; text-decoration: none; color: inherit; border-bottom: 1px solid #f0f0f0; transition: background 0.2s;" onmouseover="this.style.background='#f8f9fa'" onmouseout="this.style.background='transparent'">
                              Trombinoscope
                        </a>
                        <a href="/salary/export" style="display: block; padding: 12px 16px; text-decoration: none; color: inherit; border-bottom: 1px solid #f0f0f0; transition: background 0.2s;" onmouseover="this.style.background='#f8f9fa'" onmouseout="this.style.background='transparent'">
                              Bulletins Paie
                        </a>
                        <a href="<?php echo e(route('absences.counters')); ?>" style="display: block; padding: 12px 16px; text-decoration: none; color: inherit; border-bottom: 1px solid #f0f0f0; transition: background 0.2s;" onmouseover="this.style.background='#f8f9fa'" onmouseout="this.style.background='transparent'">
                              Compteurs Absences
                        </a>
                        <a href="<?php echo e(route('planning.monthly')); ?>" style="display: block; padding: 12px 16px; text-decoration: none; color: inherit; border-bottom: 1px solid #f0f0f0; transition: background 0.2s;" onmouseover="this.style.background='#f8f9fa'" onmouseout="this.style.background='transparent'">
                              Planning Mensuel
                        </a>
                        <a href="<?php echo e(route('planning.weekly')); ?>" style="display: block; padding: 12px 16px; text-decoration: none; color: inherit; transition: background 0.2s;" onmouseover="this.style.background='#f8f9fa'" onmouseout="this.style.background='transparent'">
                              Planning Hebdo
                        </a>
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



<!-- WhatsApp-like Chat Popup -->
            <div id="chatPopup" class="whatsapp-chat" style="display: none;">
                <div class="whatsapp-header">
                    <div class="whatsapp-avatar"><i class="fa-solid fa-robot" aria-hidden="true"></i></div>
                    <div>
                        <div class="whatsapp-title">Assistant RH</div>
                        <div class="whatsapp-subtitle">HospitalRH · OpenAI</div>
                    </div>
                    <button onclick="toggleChatPopup()" class="whatsapp-close" title="Minimiser">−</button>
                </div>
                <div class="whatsapp-suggestions">
                    <button onclick="sendSuggestion('Stats RH globales')"> Stats</button>
                    <button onclick="sendSuggestion('Salaire matricule 1')"> Salaire</button>
                    <button onclick="sendSuggestion('Planning matricule 1 avril')"> Planning</button>
                </div>
                <div class="whatsapp-messages" id="whatsappMessages">
                    <div class="whatsapp-message whatsapp-bot">
                        Bonjour ! Assistant RH prêt. Demandez stats, salaires, plannings...
                    </div>
                </div>
                <div class="whatsapp-input-area">
                    <textarea id="whatsappInput" placeholder="Tapez votre question..." rows="1" onkeydown="handleKey(event)" oninput="autoResize(this)"></textarea>
                    <button id="whatsappSend" onclick="sendWhatsAppMessage()"><i class="fa-solid fa-paper-plane" aria-hidden="true"></i></button>
                </div>
            </div>

        </main>

<?php echo $__env->make('components.chatbot', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

<style>
.assistant-rh-btn {
    position: fixed;
    bottom: 24px;
    right: 24px;
    background: linear-gradient(135deg, #1a1a2e 0%, #c8102e 100%);
    color: white;
    padding: 12px 20px;
    border-radius: 50px;
    text-decoration: none;
    font-weight: 600;
    font-size: 14px;
    box-shadow: 0 8px 32px rgba(26,26,46,0.4);
    display: flex;
    align-items: center;
    gap: 8px;
    z-index: 9999;
    transition: all 0.3s ease;
    border: none;
}

.assistant-rh-btn:hover {
    transform: translateY(-2px) scale(1.02);
    box-shadow: 0 12px 40px rgba(26,26,46,0.6);
}
</style>

<style>
.floating-chat-btn {
    position: fixed;
    bottom: 24px;
    right: 24px;
    width: 60px;
    height: 60px;
    background: linear-gradient(135deg, #1a1a2e 0%, #c8102e 100%);
    border-radius: 50%;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 4px;
    box-shadow: 0 8px 32px rgba(26, 26, 46, 0.4);
    backdrop-filter: blur(10px);
    border: 2px solid rgba(255,255,255,0.2);
    z-index: 10000;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    text-decoration: none;
    color: white;
}

.floating-chat-btn:hover {
    transform: scale(1.1);
    box-shadow: 0 16px 48px rgba(26, 26, 46, 0.6);
}

.chat-modal {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    z-index: 10001;
    animation: modalSlideIn 0.3s ease-out;
}

.chat-modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100vw;
    height: 100vh;
    background: transparent;
}

.chat-modal-content {
    position: fixed;
    top: 0;
    left: 0;
    width: 100vw;
    height: 100vh;
    background: transparent;
    border-radius: 0;
    overflow: hidden;
    display: flex;
    flex-direction: column;
    box-shadow: none;

.chat-modal-header {
    display: none;
}

.chat-close-btn {
    background: none;
    border: none;
    font-size: 20px;
    cursor: pointer;
    color: #666;
    width: 32px;
    height: 32px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s;
}

.chat-close-btn:hover {
    background: #f1f5f9;
    color: #333;
}

.chat-iframe {
    position: fixed !important;
    top: 0 !important;
    left: 0 !important;
    width: 100vw !important;
    height: 100vh !iframe !important;
    border: none !important;
    background: transparent !important;
    z-index: 10002 !important;
    margin: 0 !important;
    padding: 0 !important;
    transform: none !important;
}

@keyframes modalSlideIn {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}

@media (max-width: 768px) {
    .chat-modal-content {
        max-height: 85vh;
        border-radius: 20px 20px 0 0;
    }
}

.floating-chat-btn.active {
    box-shadow: 0 8px 32px rgba(200, 16, 46, 0.4);
}

.chat-icon {
    font-size: 24px;
    animation: pulse-chat 2s infinite;
}

.chat-badge {
    background: rgba(255,255,255,0.9);
    color: #1a1a2e;
    font-size: 9px;
    font-weight: 700;
    padding: 2px 6px;
    border-radius: 10px;
    transform: scale(0.8);
}

@keyframes pulse-chat {
    0%, 100% { transform: scale(1); }
    50% { transform: scale(1.1); }
}
</style>


<script>
function toggleChatPopup() {
    const popup = document.getElementById('chatPopup');
    const btn = document.querySelector('.floating-chat-btn');
    popup.style.display = popup.style.display === 'none' ? 'block' : 'none';
    btn.classList.toggle('active');
}

let threadId = localStorage.getItem('chatThread') || null;

function autoResize(el) {
    el.style.height = 'auto';
    el.style.height = el.scrollHeight + 'px';
}

function handleKey(e) {
    if (e.key === 'Enter' && !e.shiftKey) {
        e.preventDefault();
        sendWhatsAppMessage();
    }
}

function sendSuggestion(text) {
    document.getElementById('whatsappInput').value = text;
    sendWhatsAppMessage();
}

async function sendWhatsAppMessage() {
    const input = document.getElementById('whatsappInput');
    const btn = document.getElementById('whatsappSend');
    const messages = document.getElementById('whatsappMessages');
    const text = input.value.trim();

    if (!text) return;

    // User message
    const userMsg = document.createElement('div');
    userMsg.className = 'whatsapp-message whatsapp-user';
    userMsg.textContent = text;
    messages.appendChild(userMsg);
    messages.scrollTop = messages.scrollHeight;

    input.value = '';
    autoResize(input);
    btn.disabled = true;

    // Typing
    const typing = document.createElement('div');
    typing.className = 'whatsapp-message whatsapp-bot typing';
    typing.innerHTML = '<span class="typing-dot"></span><span class="typing-dot"></span><span class="typing-dot"></span>';
    messages.appendChild(typing);
    messages.scrollTop = messages.scrollHeight;

    try {
        const res = await fetch('/chat', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            },
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
            messages.scrollTop = messages.scrollHeight;
        } else {
            const errorMsg = document.createElement('div');
            errorMsg.className = 'whatsapp-message whatsapp-bot';
            errorMsg.textContent = 'Erreur serveur';
            messages.appendChild(errorMsg);
        }
    } catch (err) {
        typing.remove();
        const errorMsg = document.createElement('div');
        errorMsg.className = 'whatsapp-message whatsapp-bot';
        errorMsg.textContent = 'Pas de connexion';
        messages.appendChild(errorMsg);
    } finally {
        btn.disabled = false;
        input.focus();
    }
}

document.getElementById('menuToggle')?.addEventListener('click', () => {
    document.getElementById('sidebar').classList.toggle('open');
});

// Close modal on ESC
document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') toggleChatModal();
});

// Export Dropdown Toggle
function toggleExportDropdown() {
    const dropdown = document.getElementById('exportDropdown');
    dropdown.style.display = dropdown.style.display === 'none' ? 'block' : 'none';
}

// Close export dropdown when clicking outside
document.addEventListener('click', function(event) {
    const wrapper = document.querySelector('.export-wrapper');
    const dropdown = document.getElementById('exportDropdown');
    if (wrapper && !wrapper.contains(event.target)) {
        dropdown.style.display = 'none';
    }
});

// Notifications
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
    fetch('<?php echo e(route("api.notifications.data")); ?>')
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
                <a href="${item.url}" style="display: flex; align-items: center; gap: 10px; padding: 10px 16px; text-decoration: none; color: inherit; border-bottom: 1px solid #f0f0f0; transition: background 0.2s;" onmouseover="this.style.background='#f8f9fa'" onmouseout="this.style.background='transparent'">
                    <div style="width: 32px; height: 32px; border-radius: 50%; background: ${item.color}; display: flex; align-items: center; justify-content: center; color: white; font-size: 0.8rem; flex-shrink: 0;">
                        ${item.type === 'absence' ? '<i class="fa-solid fa-calendar-days" aria-hidden="true"></i>' : '<i class="fa-solid fa-newspaper" aria-hidden="true"></i>'}
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

<?php echo $__env->yieldPushContent('scripts'); ?>
</body>
</html>
<?php /**PATH D:\Projects\HospitalRh\resources\views/layouts/app.blade.php ENDPATH**/ ?>