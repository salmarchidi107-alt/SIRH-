<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Super Admin — @yield('title', 'SuperAdmin')</title>
    <style>
    /* ═══════════════════════════════════════════════════════════════════════
       RESET & BASE
    ═══════════════════════════════════════════════════════════════════════ */
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
    html, body { height: 100%; }
    body {
        font-family: 'Plus Jakarta Sans', system-ui, -apple-system, sans-serif;
        font-size: 14px;
        background: #f4f7fa;
        color: #0f172a;
        -webkit-font-smoothing: antialiased;
    }
    a { text-decoration: none; color: inherit; }
    button { font-family: inherit; cursor: pointer; border: none; background: none; }
    input, select { font-family: inherit; }

    /* ═══════════════════════════════════════════════════════════════════════
       CSS VARIABLES — couleurs HospitalRH + tokens UI
    ═══════════════════════════════════════════════════════════════════════ */
    :root {
        /* Brand HospitalRH */
        --navy:          #0d2137;
        --navy-light:    #1a3552;
        --accent:        #00c9a7;
        --accent-light:  #4fd9c0;
        --primary:       #1a8fa5;
        --primary-dark:  #094f5c;

        /* UI tokens */
        --bg:            #f4f7fa;
        --surface:       #ffffff;
        --surface-2:     #f8fafc;
        --border:        #e2e8f0;
        --border-light:  #f1f5f9;
        --text:          #0f172a;
        --text-muted:    #64748b;
        --text-light:    #94a3b8;

        /* Status */
        --success:       #10b981;
        --success-bg:    #ecfdf5;
        --warning:       #f59e0b;
        --warning-bg:    #fffbeb;
        --danger:        #ef4444;
        --danger-bg:     #fef2f2;
        --info:          #3b82f6;
        --info-bg:       #eff6ff;

        /* Shadows */
        --shadow-sm:  0 1px 3px rgba(0,0,0,.06), 0 1px 2px rgba(0,0,0,.04);
        --shadow:     0 4px 6px -1px rgba(0,0,0,.07), 0 2px 4px -1px rgba(0,0,0,.05);
        --shadow-md:  0 10px 15px -3px rgba(0,0,0,.08), 0 4px 6px -2px rgba(0,0,0,.04);

        --radius:    12px;
        --radius-sm:  8px;
    }

    /* ═══════════════════════════════════════════════════════════════════════
       LAYOUT
    ═══════════════════════════════════════════════════════════════════════ */
    .sa-layout {
        display: flex;
        height: 100vh;
        overflow: hidden;
    }

    /* ═══════════════════════════════════════════════════════════════════════
       SIDEBAR — inspiré du style des fichiers HTML de référence
    ═══════════════════════════════════════════════════════════════════════ */
    .sa-sidebar {
        width: 240px;
        background: var(--navy);
        display: flex;
        flex-direction: column;
        flex-shrink: 0;
        overflow-y: auto;
        overflow-x: hidden;
    }

    /* Brand */
    .sa-brand {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 18px 16px 14px;
        border-bottom: 1px solid rgba(0,201,167,.3);
    }
    .sa-brand-icon {
        width: 36px;
        height: 36px;
        background: linear-gradient(135deg, var(--primary), var(--accent));
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 15px;
        font-weight: 800;
        color: #fff;
        flex-shrink: 0;
        box-shadow: 0 4px 12px rgba(0,201,167,.3);
    }
    .sa-brand-name {
        font-size: 15px;
        font-weight: 800;
        color: #fff;
        line-height: 1.1;
    }
    .sa-brand-name span {
        color: var(--accent);
    }
    .sa-brand-sub {
        font-size: 9px;
        color: rgba(255,255,255,.25);
        letter-spacing: .12em;
        text-transform: uppercase;
        margin-top: 2px;
    }
    .sa-badge-sa {
        margin-left: auto;
        font-size: 9px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .06em;
        background: rgba(0,201,167,.15);
        color: var(--accent-light);
        border: 1px solid rgba(0,201,167,.25);
        border-radius: 5px;
        padding: 2px 7px;
        flex-shrink: 0;
    }

    /* Section labels */
    .sa-section {
        font-size: 10px;
        font-weight: 700;
        letter-spacing: .12em;
        text-transform: uppercase;
        color: rgba(255,255,255,.2);
        padding: 14px 16px 4px;
    }

    /* Nav items */
    .sa-nav {
        flex: 1;
        padding: 8px 0;
    }
    .sa-item {
        display: flex;
        align-items: center;
        gap: 9px;
        padding: 8px 16px;
        color: rgba(255,255,255,.45);
        font-size: 13px;
        font-weight: 500;
        transition: background .15s, color .15s;
        border-left: 3px solid transparent;
        cursor: pointer;
    }
    .sa-item:hover {
        background: rgba(255,255,255,.05);
        color: rgba(255,255,255,.8);
    }

    /* CRITIQUE : icônes fixées à 16px */
    .sa-item svg {
        width: 16px !important;
        height: 16px !important;
        flex-shrink: 0;
        display: block;
    }


    /* Footer sidebar */
    .sa-footer {
        padding: 12px 16px;
        border-top: 1px solid #1a8fa5;
    }
    .sa-user {
        display: flex;
        align-items: center;
        gap: 9px;
        margin-bottom: 10px;
    }
    .sa-avatar {
        width: 32px;
        height: 32px;
        border-radius: 9px;
        background: linear-gradient(135deg, var(--primary), var(--accent));
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 11px;
        font-weight: 800;
        color: #fff;
        flex-shrink: 0;
    }
    .sa-user-namee { font-size: 12px; font-weight: 600; color: #ffffff !important;}
    .sa-user-role { font-size: 10px; color: rgba(255,255,255,.3); margin-top: 1px; }
    .sa-logout {
        display: flex;
        align-items: center;
        gap: 7px;
        width: 100%;
        padding: 7px 10px;
        border-radius: 7px;
        font-size: 12px;
        font-weight: 500;
        color: rgba(255,255,255,.3);
        background: transparent;
        transition: background .15s, color .15s;
    }
    .sa-logout:hover { background: rgba(239,68,68,.15); color: #fca5a5; }
    .sa-logout svg { width: 14px !important; height: 14px !important; flex-shrink: 0; }

    /* ═══════════════════════════════════════════════════════════════════════
       MAIN CONTENT
    ═══════════════════════════════════════════════════════════════════════ */
    .sa-main {
        flex: 1;
        display: flex;
        flex-direction: column;
        overflow: hidden;
        background: var(--bg);
    }

    /* Topbar */
    .sa-topbar {
        background: var(--surface);
        border-bottom: 1px solid var(--border);
        padding: 0 24px;
        height: 56px;
        display: flex;
        align-items: center;
        gap: 14px;
        flex-shrink: 0;
        box-shadow: var(--shadow-sm);
    }
    .sa-topbar-title {
        font-size: 15px;
        font-weight: 800;
        color: var(--text);
        flex: 1;
    }
    .sa-topbar-badge {
        font-size: 10px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .06em;
        background: rgba(0,201,167,.12);
        color: var(--primary);
        border: 1px solid rgba(0,201,167,.25);
        border-radius: 6px;
        padding: 3px 10px;
    }

    /* Page content */
    .sa-content {
        flex: 1;
        overflow-y: auto;
        padding: 24px 28px;
    }

    /* Page header */
    .sa-page-header {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 16px;
        margin-bottom: 24px;
        flex-wrap: wrap;
    }
    .sa-page-title {
        font-size: 20px;
        font-weight: 900;
        color: var(--text);
        line-height: 1.2;
    }
    .sa-page-sub {
        font-size: 13px;
        color: var(--text-muted);
        margin-top: 3px;
    }

    /* Flash */
    .sa-flash {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 12px 20px;
        font-size: 13px;
        font-weight: 500;
        border-left: 4px solid;
        flex-shrink: 0;
    }
    .sa-flash svg { width: 15px !important; height: 15px !important; flex-shrink: 0; }
    .sa-flash.success { background: var(--success-bg); color: var(--success); border-color: var(--success); }
    .sa-flash.error   { background: var(--danger-bg);  color: var(--danger);  border-color: var(--danger); }
    .sa-flash.warning { background: var(--warning-bg); color: var(--warning); border-color: var(--warning); }

    /* ═══════════════════════════════════════════════════════════════════════
       COMPOSANTS PARTAGÉS
    ═══════════════════════════════════════════════════════════════════════ */

    /* Cards */
    .sa-card {
        background: var(--surface);
        border-radius: var(--radius);
        border: 1px solid var(--border);
        box-shadow: var(--shadow-sm);
        overflow: hidden;
    }
    .sa-card-header {
        padding: 16px 20px;
        border-bottom: 1px solid var(--border-light);
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
    }
    .sa-card-title {
        font-size: 14px;
        font-weight: 800;
        color: var(--text);
    }
    .sa-card-sub {
        font-size: 12px;
        color: var(--text-muted);
        margin-top: 1px;
    }
    .sa-card-body { padding: 20px; }

    /* Boutons */
    .sa-btn {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 8px 16px;
        border-radius: 9px;
        font-size: 13px;
        font-weight: 600;
        cursor: pointer;
        transition: all .15s;
        white-space: nowrap;
        border: 1px solid transparent;
        font-family: inherit;
        text-decoration: none;
    }
    .sa-btn svg { width: 14px !important; height: 14px !important; }

    .sa-btn-primary {
        background: linear-gradient(135deg, var(--primary), var(--primary-dark));
        color: #fff;
        box-shadow: 0 4px 12px rgba(26,143,165,.35);
    }
    .sa-btn-primary:hover { box-shadow: 0 6px 18px rgba(26,143,165,.45); transform: translateY(-1px); }

    .sa-btn-ghost {
        background: var(--surface-2);
        color: var(--text-muted);
        border-color: var(--border);
    }
    .sa-btn-ghost:hover { background: var(--border-light); color: var(--text); }

    .sa-btn-danger {
        background: var(--danger-bg);
        color: var(--danger);
        border-color: #fecaca;
    }
    .sa-btn-danger:hover { background: #fee2e2; }

    .sa-btn-sm { padding: 5px 11px; font-size: 12px; border-radius: 7px; }

    /* Inputs */
    .sa-input {
        width: 100%;
        padding: 9px 12px;
        border: 1.5px solid var(--border);
        border-radius: 9px;
        font-size: 13px;
        color: var(--text);
        background: var(--surface);
        outline: none;
        transition: border-color .2s;
        font-family: inherit;
    }
    .sa-input:focus { border-color: var(--primary); box-shadow: 0 0 0 3px rgba(26,143,165,.1); }
    .sa-input::placeholder { color: var(--text-light); }

    .sa-label {
        display: block;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .06em;
        color: var(--text-muted);
        margin-bottom: 5px;
    }
    .sa-field { margin-bottom: 16px; }
    .sa-field:last-child { margin-bottom: 0; }
    .sa-error { font-size: 11px; color: var(--danger); font-weight: 500; margin-top: 4px; }

    /* Badges status */
    .sa-badge {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        font-size: 11px;
        font-weight: 600;
        padding: 3px 9px;
        border-radius: 9999px;
    }
    .sa-badge-active    { background: var(--success-bg); color: var(--success); }
    .sa-badge-suspended { background: var(--warning-bg); color: var(--warning); }
    .sa-badge-trial     { background: var(--info-bg);    color: var(--info); }
    .sa-badge-inactive  { background: var(--surface-2);  color: var(--text-muted); }
    .sa-badge-ent       { background: #1a8fa5; color: #a5b4fc; }
    .sa-badge-pro       { background: #1c1917; color: #fcd34d; }
    .sa-badge-starter   { background: var(--surface-2); color: var(--text-muted); }

    /* Usage bar */
    .sa-usage-bar { height: 4px; background: var(--border-light); border-radius: 2px; overflow: hidden; flex: 1; }
    .sa-usage-fill { height: 100%; border-radius: 2px; transition: width .4s; }

    /* Table */
    .sa-table { width: 100%; border-collapse: collapse; }
    .sa-table th {
        text-align: left;
        padding: 10px 16px;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .06em;
        color: var(--text-muted);
        border-bottom: 1px solid var(--border-light);
    }
    .sa-table td {
        padding: 13px 16px;
        border-bottom: 1px solid var(--border-light);
        font-size: 13px;
        vertical-align: middle;
    }
    .sa-table tr:last-child td { border-bottom: none; }
    .sa-table tr:hover td { background: var(--surface-2); }

    /* Pills filter */
    .sa-pill {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 5px 13px;
        border-radius: 9999px;
        font-size: 12px;
        font-weight: 600;
        border: 1.5px solid var(--border);
        background: var(--surface);
        color: var(--text-muted);
        text-decoration: none;
        cursor: pointer;
        transition: all .15s;
    }
    .sa-pill:hover { border-color: var(--primary); color: var(--primary); }
    .sa-pill.on { background: var(--navy); color: #fff; border-color: var(--navy); }

    /* Search box */
    .sa-search {
        display: flex;
        align-items: center;
        gap: 8px;
        background: var(--surface);
        border: 1.5px solid var(--border);
        border-radius: 10px;
        padding: 7px 13px;
        transition: border-color .2s;
    }
    .sa-search:focus-within { border-color: var(--primary); }
    .sa-search input {
        border: none;
        background: transparent;
        font-size: 13px;
        outline: none;
        color: var(--text);
        width: 200px;
        font-family: inherit;
    }
    .sa-search input::placeholder { color: var(--text-light); }
    .sa-search svg { width: 14px !important; height: 14px !important; color: var(--text-light); flex-shrink: 0; }

    /* Tenant card */
    .sa-tenant-card {
        background: var(--surface);
        border-radius: var(--radius);
        border: 1px solid var(--border);
        overflow: hidden;
        transition: border-color .2s, box-shadow .2s;
    }
    .sa-tenant-card:hover { border-color: var(--primary); box-shadow: var(--shadow-md); }

    /* Radio option */
    .sa-radio-opt {
        display: flex;
        align-items: flex-start;
        gap: 12px;
        padding: 12px 14px;
        border: 1.5px solid var(--border);
        border-radius: 10px;
        cursor: pointer;
        margin-bottom: 8px;
        transition: border-color .2s, background .2s;
    }
    .sa-radio-opt.selected { border-color: var(--primary); background: rgba(26,143,165,.04); }
    .sa-radio-dot {
        width: 18px; height: 18px;
        border-radius: 50%;
        border: 2px solid var(--border);
        display: flex; align-items: center; justify-content: center;
        flex-shrink: 0; margin-top: 1px;
        transition: all .15s;
    }
    .sa-radio-dot.on { border-color: var(--primary); background: var(--primary); }
    .sa-radio-dot.on::after { content: ''; width: 7px; height: 7px; background: #fff; border-radius: 50%; }

    /* Upload zone */
    .sa-upload {
        border: 2px dashed var(--border);
        border-radius: 10px;
        padding: 22px;
        text-align: center;
        cursor: pointer;
        transition: border-color .2s, background .2s;
        background: var(--surface-2);
    }
    .sa-upload:hover { border-color: var(--primary); background: rgba(26,143,165,.03); }

    /* Color swatch */
    .sa-swatch {
        width: 24px; height: 24px;
        border-radius: 6px;
        cursor: pointer;
        border: 2px solid transparent;
        transition: transform .15s, border-color .15s;
        display: inline-block;
        flex-shrink: 0;
    }
    .sa-swatch:hover { transform: scale(1.18); }
    .sa-swatch.active { border-color: var(--text); }

    /* Preview sidebar */
    .sa-preview-sidebar {
        background: var(--navy);
        border-radius: 10px;
        overflow: hidden;
    }
    .sa-preview-topbar {
        padding: 11px 14px;
        border-bottom: 1px solid rgba(255,255,255,.07);
        display: flex;
        align-items: center;
        gap: 9px;
    }
    .sa-preview-logo {
        width: 28px; height: 28px;
        border-radius: 7px;
        display: flex; align-items: center; justify-content: center;
        font-size: 11px; font-weight: 800; color: #fff;
        flex-shrink: 0;
        transition: background .3s;
    }
    .sa-preview-item { padding: 7px 14px; font-size: 11px; color: rgba(255,255,255,.35); }
    .sa-preview-item.act { color: var(--accent-light); background: rgba(0,201,167,.1); }

    /* Login preview */
    .sa-preview-login {
        background: var(--surface-2);
        border-radius: 8px;
        padding: 14px;
        margin-top: 12px;
    }
    .sa-preview-login-logo {
        width: 32px; height: 32px;
        border-radius: 8px;
        display: flex; align-items: center; justify-content: center;
        font-size: 13px; font-weight: 800; color: #fff;
        margin-bottom: 8px;
        transition: background .3s;
    }
    .sa-fake-input { background: var(--border-light); border-radius: 6px; height: 26px; margin-bottom: 6px; }
    .sa-fake-btn {
        height: 28px; border-radius: 6px;
        display: flex; align-items: center; justify-content: center;
        font-size: 11px; font-weight: 700; color: #fff;
        transition: background .3s;
    }

    /* Stat card */
    .sa-stat {
        background: var(--surface);
        border-radius: var(--radius);
        border: 1px solid var(--border);
        padding: 16px 18px;
        box-shadow: var(--shadow-sm);
    }
    .sa-stat-icon {
        width: 38px; height: 38px;
        border-radius: 10px;
        display: flex; align-items: center; justify-content: center;
        flex-shrink: 0;
        margin-bottom: 10px;
    }
    .sa-stat-icon svg { width: 18px !important; height: 18px !important; }
    .sa-stat-val { font-size: 26px; font-weight: 900; color: var(--text); line-height: 1; }
    .sa-stat-lbl { font-size: 12px; color: var(--text-muted); margin-top: 4px; font-weight: 500; }

    /* Info tag */
    .sa-tag {
        display: inline-flex; align-items: center; gap: 4px;
        background: var(--surface-2);
        border: 1px solid var(--border);
        border-radius: 6px;
        padding: 3px 8px;
        font-size: 11px; color: var(--text-muted); font-weight: 600;
    }

    /* Breadcrumb */
    .sa-breadcrumb {
        display: flex; align-items: center; gap: 5px;
        font-size: 12px; color: var(--text-muted);
        margin-bottom: 8px;
    }
    .sa-breadcrumb a { color: var(--text-muted); transition: color .15s; }
    .sa-breadcrumb a:hover { color: var(--primary); }
    .sa-breadcrumb svg { width: 12px !important; height: 12px !important; }
    </style>
    @stack('styles')
</head>
<body>
<div class="sa-layout">

    {{-- ═══════════════════════════════════════════════════════ SIDEBAR ═══ --}}
    <aside class="sa-sidebar">

        {{-- Brand --}}
        <div class="sa-brand">
            <div class="sa-brand-icon">SA</div>
            <div>
                <div class="sa-brand-name">Super<span>Admin</span></div>
                <div class="sa-brand-sub">Super Admin</div>
            </div>
            <span class="sa-badge-sa">SA</span>
        </div>

        {{-- Nav --}}
        <div class="sa-nav">
            <div class="sa-section">Principal</div>

            <a href="{{ route('superadmin.dashboard') }}"
               class="sa-item {{ request()->routeIs('superadmin.dashboard') ? 'active' : '' }}">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <rect x="3" y="3" width="7" height="7" rx="1"/>
                    <rect x="14" y="3" width="7" height="7" rx="1"/>
                    <rect x="14" y="14" width="7" height="7" rx="1"/>
                    <rect x="3" y="14" width="7" height="7" rx="1"/>
                </svg>
                Dashboard
            </a>

            <div class="sa-section">Tenants</div>

            <a href="{{ route('superadmin.tenants.index') }}"
               class="sa-item {{ request()->routeIs('superadmin.tenants*') ? 'active' : '' }}">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <rect x="2" y="7" width="20" height="14" rx="2"/>
                    <path d="M16 7V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v2"/>
                </svg>
                Gestion des tenants
            </a>

            <a href="{{ route('superadmin.tenants.create') }}"
               class="sa-item {{ request()->routeIs('superadmin.tenants.create') ? 'active' : '' }}">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="10"/>
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v8M8 12h8"/>
                </svg>
                Ajouter un nouveau tenant
            </a>

            <div class="sa-section">Clients</div>

            <a href="{{ route('superadmin.clients.index') }}"
               class="sa-item {{ request()->routeIs('superadmin.clients*') ? 'active' : '' }}">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                    <circle cx="9" cy="7" r="4"/>
                    <path d="M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/>
                </svg>
                Tous les clients
            </a>
            <a href="{{ route('superadmin.roles.index') }}"
               class="sa-item {{ request()->routeIs('superadmin.roles*') ? 'active' : '' }}">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                </svg>
                Rôles
            </a>

            <div class="sa-section">Configuration</div>

            <a href="{{ route('superadmin.settings.index') }}"
               class="sa-item {{ request()->routeIs('superadmin.settings*') ? 'active' : '' }}">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="3"/>
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 1v4M12 19v4M4.22 4.22l2.83 2.83M16.95 16.95l2.83 2.83M1 12h4M19 12h4M4.22 19.78l2.83-2.83M16.95 7.05l2.83-2.83"/>
                </svg>
                Paramètres
            </a>
        </div>

        {{-- Footer --}}
        <div class="sa-footer">
            <div class="sa-user">
                <div class="sa-avatar">{{ strtoupper(substr(auth()->user()->name ?? 'SA', 0, 2)) }}</div>
                <div>
                    <div class="sa-user-namee">{{ auth()->user()->name ?? 'Super Admin' }}</div>
                </div>
            </div>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="sa-logout">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                    </svg>
                    Déconnexion
                </button>
            </form>
        </div>
    </aside>

    {{-- ═══ MAIN CONTENT ═══ --}}
    <div class="sa-main">

        {{-- Topbar --}}
        <div class="sa-topbar">
            <div class="sa-topbar-title">@yield('page-title', 'Gestion des Accès')</div>
            <span class="sa-topbar-badge">Super Admin </span>

        </div>

        {{-- Flash messages --}}
        @if(session('success'))
        <div class="sa-flash success">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            {{ session('success') }}
        </div>
        @endif
        @if(session('error'))
        <div class="sa-flash error">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                <circle cx="12" cy="12" r="10"/><path d="M12 8v4M12 16h.01"/>
            </svg>
            {{ session('error') }}
        </div>
        @endif
        @if(session('warning'))
        <div class="sa-flash warning">⚠ {{ session('warning') }}</div>
        @endif

        {{-- Content --}}
        <div class="sa-content">
            <div class="sa-page-header">
                <div>@yield('page-header')</div>
                <div>@yield('page-actions')</div>
            </div>
            @yield('content')
        </div>
    </div>
</div>
@stack('scripts')
</body>
</html>
