<?php $__env->startSection('title', 'Pointage — Badgeuse'); ?>

<?php $__env->startPush('styles'); ?>
<style>
    :root {
        --p-bg:          #f8fafc;
        --p-surface:     #ffffff;
        --p-border:      #e2e8f0;
        --p-border-soft: #f1f5f9;
        --p-text:        #0f172a;
        --p-text-muted:  #64748b;
        --p-text-light:  #94a3b8;
        --p-teal:        #0d9488;
        --p-teal-bg:     #f0fdfa;
        --p-teal-light:  #ccfbf1;
        --p-blue:        #1d4ed8;
        --p-blue-bg:     #eff6ff;
        --p-purple:      #0daba6;
        --p-purple-bg:   #f5f3ff;
        --p-amber:       #d97706;
        --p-amber-bg:    #fffbeb;
        --p-red:         #dc2626;
        --p-red-bg:      #fef2f2;
        --p-green:       #16a34a;
        --p-green-bg:    #f0fdf4;
        --p-gray-bg:     #f8fafc;

        /* ── Couleurs shift ── */
        --shift-normal-bg:     #f0fdf4;
        --shift-normal-border: #bbf7d0;
        --shift-normal-text:   #15803d;
        --shift-normal-dot:    #22c55e;
        --shift-normal-row:    #f7fef9;

        --shift-garde-bg:      #faf5ff;
        --shift-garde-border:  #e9d5ff;
        --shift-garde-text:    #7c3aed;
        --shift-garde-dot:     #a855f7;
        --shift-garde-row:     #fdf8ff;
    }

    .pointage-wrap { display:flex; flex-direction:column; height:calc(100vh - 64px); background:var(--p-bg); }

    /* ── Topbar ── */
    .pt-topbar {
        background:var(--p-surface); border-bottom:1px solid var(--p-border);
        padding:0 1.5rem; height:52px;
        display:flex; align-items:center; justify-content:space-between; flex-shrink:0;
    }
    .pt-topbar-left  { display:flex; align-items:center; gap:1rem; }
    .pt-topbar-right { display:flex; align-items:center; gap:.75rem; }
    .pt-title { font-size:15px; font-weight:600; color:var(--p-text); }

    .pt-tabs { display:flex; background:var(--p-bg); border:1px solid var(--p-border); border-radius:8px; overflow:hidden; }
    .pt-tab {
        padding:5px 16px; font-size:12px; font-weight:500; cursor:pointer;
        color:var(--p-text-muted); background:transparent; border:none;
        text-decoration:none; display:flex; align-items:center; transition:all .15s;
    }
    .pt-tab.active, .pt-tab:hover { background:var(--p-teal); color:#fff; }

    .pt-btn-validate {
        background:var(--p-teal); color:#fff; border:none;
        padding:7px 16px; border-radius:8px; font-size:13px; font-weight:600;
        cursor:pointer; transition:background .15s;
    }
    .pt-btn-validate:hover { background:#0f766e; }

    /* ── Week nav ── */
    .pt-weeknav {
        background:var(--p-surface); border-bottom:1px solid var(--p-border);
        padding:.75rem 1.5rem; display:flex; align-items:center; gap:.75rem; flex-shrink:0;
    }
    .pt-weeknav-btn {
        background:var(--p-bg); border:1px solid var(--p-border);
        width:28px; height:28px; border-radius:6px; cursor:pointer;
        display:flex; align-items:center; justify-content:center;
        font-size:15px; color:var(--p-text-muted); transition:all .15s; text-decoration:none;
    }
    .pt-weeknav-btn:hover { border-color:var(--p-teal); color:var(--p-teal); }
    .pt-week-label { font-size:13px; font-weight:500; color:var(--p-text); }
    .pt-week-badge {
        background:var(--p-teal-light); color:var(--p-teal);
        font-size:11px; font-weight:600; padding:2px 10px; border-radius:20px;
    }

    /* ══════════════════════════════════════════════════════
       BARRE LÉGENDE + FILTRE SHIFT
    ══════════════════════════════════════════════════════ */
    .shift-legend-bar {
        background: var(--p-surface);
        border-bottom: 1px solid var(--p-border);
        padding: .55rem 1.5rem;
        display: flex; align-items: center; gap: 1.25rem; flex-shrink: 0;
    }
    .shift-legend-title {
        font-size: 11px; font-weight: 600; color: var(--p-text-muted);
        text-transform: uppercase; letter-spacing: .06em; white-space: nowrap;
    }
    .shift-legend-item {
        display: flex; align-items: center; gap: 6px;
        font-size: 12px; font-weight: 500;
    }
    .shift-legend-dot {
        width: 10px; height: 10px; border-radius: 50%; flex-shrink: 0;
    }
    .shift-legend-item.normal { color: var(--shift-normal-text); }
    .shift-legend-item.garde  { color: var(--shift-garde-text); }
    .shift-legend-item.normal .shift-legend-dot { background: var(--shift-normal-dot); }
    .shift-legend-item.garde  .shift-legend-dot { background: var(--shift-garde-dot); }

    /* Filtre rapide pills */
    .shift-filter-pills { display: flex; align-items: center; gap: 4px; margin-left: auto; }
    .shift-filter-pill {
        padding: 4px 14px; border-radius: 99px;
        font-size: 11px; font-weight: 600; cursor: pointer;
        border: 1px solid transparent; transition: all .15s; text-decoration: none;
        white-space: nowrap;
    }
    .shift-filter-pill.all {
        background: var(--p-bg); border-color: var(--p-border); color: var(--p-text-muted);
    }
    .shift-filter-pill.normal {
        background: var(--shift-normal-bg); border-color: var(--shift-normal-border); color: var(--shift-normal-text);
    }
    .shift-filter-pill.garde {
        background: var(--shift-garde-bg); border-color: var(--shift-garde-border); color: var(--shift-garde-text);
    }
    .shift-filter-pill.active { box-shadow: 0 0 0 2px currentColor; font-weight: 700; }

    /* ══════════════════════════════════════════════════════
    /* ══════════════════════════════════════════════════════
       BANNIÈRE ALERTE LOCALISATION — cliquable
    ══════════════════════════════════════════════════════ */
    .geo-alert-banner {
        background: #fef2f2; border-bottom: 2px solid #fca5a5;
        padding: .55rem 1.5rem; flex-shrink: 0;
        cursor: pointer; transition: background .15s; user-select: none;
    }
    .geo-alert-banner:hover { background: #fee2e2; }
    .geo-alert-banner-inner { display: flex; align-items: center; gap: 10px; }
    .geo-alert-title { font-size: 13px; font-weight: 700; color: #b91c1c; }
    .geo-alert-count-badge {
        background: #dc2626; color: #fff; font-size: 12px; font-weight: 800;
        padding: 2px 10px; border-radius: 99px; margin-left: 4px;
    }
    .geo-alert-caret {
        margin-left: auto; font-size: 11px; color: #b91c1c;
        display: flex; align-items: center; gap: 6px;
        font-weight: 600; white-space: nowrap;
    }
    /* ── Modal alerte géoloc / photo (structure partagée) ── */
    .geo-modal-overlay {
        display: none; position: fixed; inset: 0; z-index: 9999;
        background: rgba(0,0,0,.5); align-items: center; justify-content: center;
    }
    .geo-modal-overlay.open { display: flex; }
    .geo-modal {
        background: #fff; border-radius: 14px; width: 92%; max-width: 720px;
        max-height: 85vh; display: flex; flex-direction: column;
        box-shadow: 0 20px 60px rgba(0,0,0,.25); overflow: hidden;
    }
    .geo-modal-header {
        background: #fef2f2; border-bottom: 1px solid #fecaca;
        padding: 14px 20px; display: flex; align-items: center; gap: 10px; flex-shrink: 0;
    }
    .geo-modal-header h2 { font-size: 15px; font-weight: 700; color: #b91c1c; margin: 0; flex: 1; }
    .geo-modal-close {
        background: none; border: none; font-size: 20px; color: #b91c1c;
        cursor: pointer; line-height: 1; padding: 0;
    }
    .geo-modal-body { flex: 1; overflow-y: auto; }
    .geo-modal-row {
        display: flex; align-items: center; gap: 14px;
        padding: 13px 20px; border-bottom: 1px solid #fef2f2; transition: background .12s;
    }
    .geo-modal-row:last-child { border-bottom: none; }
    .geo-modal-row:hover { background: #fff5f5; }
    .geo-modal-avatar {
        width: 40px; height: 40px; border-radius: 50%; flex-shrink: 0;
        display: flex; align-items: center; justify-content: center;
        font-size: 13px; font-weight: 700; background: #fee2e2; color: #b91c1c;
    }
    .geo-modal-emp-name { font-size: 13px; font-weight: 700; color: #0f172a; }
    .geo-modal-emp-sub  { font-size: 11px; color: #64748b; margin-top: 2px; max-width: 360px; }
    .geo-modal-dist { margin-left: auto; text-align: right; flex-shrink: 0; }
    .geo-modal-dist-val { font-size: 16px; font-weight: 800; color: #dc2626; }
    .geo-modal-dist-label { font-size: 10px; color: #94a3b8; margin-top: 1px; }
    .geo-modal-maps {
        display: inline-flex; align-items: center; gap: 5px;
        margin-top: 7px; padding: 4px 10px; border-radius: 6px;
        background: #fff; border: 1px solid #fecaca;
        font-size: 11px; font-weight: 600; color: #dc2626;
        text-decoration: none; transition: all .12s;
    }
    .geo-modal-maps:hover { background: #fef2f2; }
    .geo-modal-footer {
        padding: 11px 20px; border-top: 1px solid #e2e8f0; background: #f8fafc;
        font-size: 11px; color: #94a3b8; flex-shrink: 0;
        display: flex; align-items: center; justify-content: space-between;
    }

    /* ── Modal photo (variante teal, réutilise .geo-modal-*) ── */
    .photo-modal-header {
        background: var(--p-teal-bg); border-bottom: 1px solid var(--p-teal-light);
    }
    .photo-modal-header h2 { color: var(--p-teal); }
    .photo-modal-close { color: var(--p-teal); }
    .photo-modal-body { padding: 22px; text-align: center; }
    .photo-modal-img { max-width: 100%; max-height: 420px; border-radius: 10px; border: 1px solid var(--p-border); }
    .photo-modal-placeholder { padding: 48px 20px; color: var(--p-text-muted); font-size: 13px; }

    /* ── Body ── */
    .pt-body { display:flex; flex:1; overflow:hidden; }

    /* ── Day sidebar ── */
    .pt-days {
        width:165px; flex-shrink:0; background:var(--p-surface);
        border-right:1px solid var(--p-border); overflow-y:auto;
    }
    .pt-day {
        display:flex; align-items:flex-start; justify-content:space-between;
        padding:11px 14px; cursor:pointer; border-left:3px solid transparent;
        text-decoration:none; transition:all .12s; border-bottom:1px solid var(--p-border-soft);
    }
    .pt-day:hover { background:var(--p-teal-bg); }
    .pt-day.active { background:var(--p-teal-bg); border-left-color:var(--p-teal); }
    .pt-day-name { font-size:12px; font-weight:600; color:var(--p-text); }
    .pt-day-date { font-size:11px; color:var(--p-text-muted); margin-top:1px; }
    .pt-day-validator {
        font-size:10px; color:var(--p-teal); margin-top:4px;
        display:flex; align-items:center; gap:2px; font-weight:500;
    }
    .pt-day-validator-time { font-size:9px; color:var(--p-text-light); margin-top:1px; }
    .pt-day-check {
        width:20px; height:20px; border-radius:50%; flex-shrink:0;
        display:flex; align-items:center; justify-content:center; font-size:10px; margin-top:1px;
    }
    .pt-day-check.ok      { background:var(--p-teal); color:#fff; }
    .pt-day-check.pending { border:1.5px solid var(--p-border); color:var(--p-text-light); }

    /* ── Table ── */
    .pt-table-wrap { flex:1; overflow:auto; }
    .pt-table { width:100%; border-collapse:collapse; min-width:1060px; }
    .pt-table thead th {
        position:sticky; top:0; z-index:2;
        background:var(--p-gray-bg); border-bottom:1px solid var(--p-border);
        padding:10px 12px; text-align:left; white-space:nowrap;
        font-size:11px; font-weight:600; text-transform:uppercase;
        letter-spacing:.05em; color:var(--p-text-muted);
    }
    .pt-table td {
        padding:10px 12px; border-bottom:1px solid var(--p-border-soft); vertical-align:middle;
    }

    /* ══════════════════════════════════════════════════════
       COULEUR PAR TYPE DE SHIFT
    ══════════════════════════════════════════════════════ */
    .pt-table tbody tr.row-shift-normal td {
        background: var(--shift-normal-row);
    }
    .pt-table tbody tr.row-shift-normal td:first-child {
        border-left: 3px solid var(--shift-normal-dot);
        padding-left: 10px;
    }
    .pt-table tbody tr.row-shift-garde td {
        background: var(--shift-garde-row);
    }
    .pt-table tbody tr.row-shift-garde td:first-child {
        border-left: 3px solid var(--shift-garde-dot);
        padding-left: 10px;
    }
    .pt-table tbody tr.row-shift-normal:hover td { filter: brightness(.97); }
    .pt-table tbody tr.row-shift-garde:hover td  { filter: brightness(.97); }

    /* Ligne avec alerte géoloc — léger liseré rouge en plus de la couleur shift */
    .pt-table tbody tr.row-geo-alert td:first-child {
        box-shadow: inset 3px 0 0 var(--p-red);
    }

    /* ── Badge shift type inline dans colonne Employé ── */
    .shift-type-pill {
        display: inline-flex; align-items: center; gap: 4px;
        padding: 2px 8px; border-radius: 99px;
        font-size: 10px; font-weight: 700; letter-spacing: .03em;
        margin-top: 3px; white-space: nowrap; line-height: 1.6;
    }
    .shift-type-pill.normal {
        background: var(--shift-normal-bg);
        border: 1px solid var(--shift-normal-border);
        color: var(--shift-normal-text);
    }
    .shift-type-pill.garde {
        background: var(--shift-garde-bg);
        border: 1px solid var(--shift-garde-border);
        color: var(--shift-garde-text);
    }
    .shift-type-pill .pill-dot {
        width: 5px; height: 5px; border-radius: 50%; flex-shrink: 0;
    }
    .shift-type-pill.normal .pill-dot { background: var(--shift-normal-dot); }
    .shift-type-pill.garde  .pill-dot { background: var(--shift-garde-dot); }

    /* ── Time pills ── */
    .pt-time-pill {
        display:inline-block; padding:3px 9px; border-radius:6px;
        font-size:12px; font-weight:700; letter-spacing:.02em;
    }
    .pt-pill-start    { background:var(--p-blue-bg);   color:var(--p-blue); }
    .pt-pill-end      { background:var(--p-purple-bg); color:var(--p-purple); }
    .pt-pill-midnight { background:#ede9fe; color:#6d28d9; }
    .pt-time-sep { color:var(--p-text-light); font-size:12px; margin:0 2px; }

    .pt-pause { display:inline-block; padding:3px 9px; border-radius:6px; font-size:12px; font-weight:600; }
    .pt-pause-on  { background:var(--p-amber-bg); color:var(--p-amber); }
    .pt-pause-off { background:var(--p-green-bg); color:var(--p-green); }

    .pt-badge { display:inline-block; padding:3px 10px; border-radius:6px; font-size:11px; font-weight:500; }
    .pt-badge-absent  { background:var(--p-red-bg);  color:var(--p-red); }
    .pt-badge-nobadge { background:var(--p-gray-bg); color:var(--p-text-muted); border:1px solid var(--p-border); }

    .pt-total      { font-size:13px; font-weight:700; color:var(--p-teal); }
    .pt-total.long { color:var(--p-amber); }

    .pt-check {
        width:22px; height:22px; border-radius:50%; display:flex;
        align-items:center; justify-content:center; cursor:pointer;
        border:none; transition:all .15s;
    }
    .pt-check.ok      { background:var(--p-teal); color:#fff; }
    .pt-check.pending { background:transparent; border:1.5px solid var(--p-border); color:var(--p-text-light); }

    .pt-action-btn {
        font-size:11px; font-weight:500; padding:4px 10px; border-radius:6px;
        cursor:pointer; border:1px solid var(--p-border); background:var(--p-surface);
        color:var(--p-text-muted); transition:all .15s; white-space:nowrap;
    }
    .pt-action-btn:hover { border-color:var(--p-teal); color:var(--p-teal); }
    .pt-action-btn.keep { background:var(--p-teal-bg); border-color:var(--p-teal); color:var(--p-teal); }

    /* ── Avatar ── */
    .pt-avatar {
        width:30px; height:30px; border-radius:50%; flex-shrink:0;
        display:flex; align-items:center; justify-content:center;
        font-size:11px; font-weight:700;
    }
    .pt-avatar.normal { background:var(--p-teal-light); color:var(--p-teal); }
    .pt-avatar.garde  { background:#f3e8ff; color:#7c3aed; }

    /* ── Status bar ── */
    .pt-statusbar {
        background:var(--p-surface); border-top:1px solid var(--p-border);
        padding:.5rem 1.5rem; display:flex; align-items:center;
        justify-content:space-between; flex-shrink:0;
    }
    .pt-stat { display:flex; align-items:center; gap:6px; font-size:12px; color:var(--p-text-muted); }
    .pt-stat-dot { width:8px; height:8px; border-radius:50%; }
    .pt-stat strong { font-weight:600; }

    .pt-row-dimmed td { opacity:.55; }
    .absent-checkbox          { accent-color:var(--p-red); width:15px; height:15px; cursor:pointer; }
    .absent-checkbox:disabled { opacity:.5; cursor:wait; }

    /* ── Géoloc tooltip — position fixed calculée en JS (évite le clipping/arrière-plan dans le tableau scrollable) ── */
    .geo-tooltip-wrap { position: relative; display: inline-block; }
    .geo-tooltip {
        display: none; position: fixed; left: 0; top: 0;
        transform: translateX(-50%);
        background: #0f172a; color: #fff;
        border-radius: 10px; padding: 12px 16px;
        font-size: 12px; line-height: 1.5;
        white-space: nowrap; z-index: 100000;
        box-shadow: 0 8px 24px rgba(0,0,0,.3); min-width: 200px;
        pointer-events: none;
    }
    .geo-tooltip::after {
        content: ''; position: absolute; top: 100%; left: 50%; transform: translateX(-50%);
        border: 6px solid transparent; border-top-color: #0f172a;
    }
    .geo-tooltip-row { display: flex; justify-content: space-between; gap: 16px; margin-bottom: 4px; }
    .geo-tooltip-row:last-child { margin-bottom: 0; }
    .geo-tooltip-label { color: #94a3b8; font-size: 10px; text-transform: uppercase; letter-spacing: .06em; }
    .geo-tooltip-val   { color: #fff; font-weight: 600; font-size: 11px; }
    .geo-tooltip-alert {
        background: #7f1d1d; color: #fecaca; padding: 6px 8px; border-radius: 6px;
        font-size: 10.5px; font-weight: 600; margin-top: 8px; white-space: normal; max-width: 220px;
    }
    .geo-maps-link {
        display: block; margin-top: 8px; color: #5eead4; font-size: 10px;
        text-decoration: none; border-top: 1px solid #1e293b; padding-top: 8px;
    }
    .geo-maps-link:hover { color: #99f6e4; }
    .pt-pdf-dropdown { position: relative; }
.pt-btn-pdf {
    background:#e2e8f0; color:#0f172a; padding:7px 14px; border-radius:8px;
    font-size:13px; font-weight:600; border:1px solid var(--p-border);
    cursor:pointer; display:flex; align-items:center; gap:6px;
}
.pt-btn-pdf:hover { background:#d6dde6; }
.pt-pdf-caret { font-size:9px; transition: transform .15s; }
.pt-pdf-dropdown.open .pt-pdf-caret { transform: rotate(180deg); }
.pt-pdf-menu {
    display:none; position:absolute; top:calc(100% + 6px); right:0;
    background:#fff; border:1px solid var(--p-border); border-radius:8px;
    box-shadow:0 8px 24px rgba(0,0,0,.12); min-width:190px; z-index:50; overflow:hidden;
}
.pt-pdf-dropdown.open .pt-pdf-menu { display:block; }
.pt-pdf-item {
    display:flex; flex-direction:column; gap:1px; padding:8px 12px;
    font-size:12.5px; font-weight:600; color:var(--p-text); text-decoration:none;
    border-bottom:1px solid var(--p-border-soft);
}
.pt-pdf-item:last-child { border-bottom:none; }
.pt-pdf-item span { font-size:10.5px; font-weight:400; color:var(--p-text-muted); }
.pt-pdf-item:hover { background:var(--p-teal-bg); color:var(--p-teal); }
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
<div class="pointage-wrap">

    
    <div class="pt-topbar">
        <div class="pt-topbar-left">
            <span class="pt-title">Pointage — Badgeuse</span>
            <div class="pt-tabs">
                <a href="<?php echo e(route('pointage.index', array_merge(request()->only(['search','department','shift']), ['date' => $currentDate->toDateString(), 'vue' => 'tous']))); ?>"
                   class="pt-tab <?php echo e(($vue ?? 'tous') === 'tous' ? 'active' : ''); ?>">Tous</a>
                <a href="<?php echo e(route('pointage.index', array_merge(request()->only(['search','department','shift']), ['date' => $currentDate->toDateString(), 'vue' => 'pointe']))); ?>"
                   class="pt-tab <?php echo e(($vue ?? '') === 'pointe' ? 'active' : ''); ?>">Pointé</a>
                <a href="<?php echo e(route('pointage.index', array_merge(request()->only(['search','department','shift']), ['date' => $currentDate->toDateString(), 'vue' => 'non_pointe']))); ?>"
                   class="pt-tab <?php echo e(($vue ?? '') === 'non_pointe' ? 'active' : ''); ?>">Non pointé</a>
            </div>
        </div>
        <div class="pt-topbar-right">
            <div class="pt-pdf-dropdown">
    <button type="button" class="pt-btn-pdf" id="btn-pdf-toggle">
        PDF <span class="pt-pdf-caret">▾</span>
    </button>
    <div class="pt-pdf-menu" id="pdf-menu">
        <a class="pt-pdf-item" href="<?php echo e(route('pointage.pdf', array_merge(request()->only(['date','department','search','vue','shift']), ['periode' => 'jour']))); ?>">
             Jour <span><?php echo e($currentDate->translatedFormat('d M Y')); ?></span>
        </a>
        <a class="pt-pdf-item" href="<?php echo e(route('pointage.pdf', array_merge(request()->only(['date','department','search','vue','shift']), ['periode' => 'semaine']))); ?>">
             Semaine <span><?php echo e($startOfWeek->format('d/m')); ?> – <?php echo e($endOfWeek->format('d/m')); ?></span>
        </a>
        <a class="pt-pdf-item" href="<?php echo e(route('pointage.pdf', array_merge(request()->only(['date','department','search','vue','shift']), ['periode' => 'mois']))); ?>">
             Mois <span><?php echo e($currentDate->translatedFormat('F Y')); ?></span>
        </a>
    </div>
</div>
            <a href="<?php echo e(route('pointage.badges-pin')); ?>"
               style="background:#9CC4B7;color:white;padding:7px 14px;border-radius:8px;font-size:13px;font-weight:600;text-decoration:none;">
                Badges PIN
            </a>
            <button class="pt-btn-validate" id="btn-validate"
                    data-date="<?php echo e($currentDate->toDateString()); ?>"
                    data-url="<?php echo e(route('pointage.valider-journee')); ?>">
                ✓ Valider la journée
            </button>
        </div>
    </div>

    
    <div style="background:var(--p-surface);border-bottom:1px solid var(--p-border);padding:.75rem 1.5rem;display:flex;gap:.75rem;align-items:center;font-size:13px;">
        <strong>Filtrer :</strong>
        <form method="GET" action="<?php echo e(route('pointage.index')); ?>" style="display:flex;gap:.5rem;align-items:center;flex:1;">
            <input type="hidden" name="date"  value="<?php echo e($currentDate->toDateString()); ?>">
            <input type="hidden" name="vue"   value="<?php echo e($vue ?? 'tous'); ?>">
            <input type="hidden" name="shift" value="<?php echo e(request('shift')); ?>">
            <input type="text" name="search" placeholder="Nom employé…"
                   value="<?php echo e(request('search')); ?>" onchange="this.form.submit()"
                   style="flex:1;padding:.5rem;border:1px solid var(--p-border);border-radius:6px;">
            <select name="department" onchange="this.form.submit()"
                    style="padding:.5rem;border:1px solid var(--p-border);border-radius:6px;">
                <option value="">Tous départements</option>
                <?php $__currentLoopData = $departments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $dept): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <option value="<?php echo e($dept); ?>" <?php echo e(request('department') == $dept ? 'selected' : ''); ?>><?php echo e($dept); ?></option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
            <?php if(request()->hasAny(['search','department'])): ?>
            <a href="<?php echo e(route('pointage.index', ['date' => $currentDate->toDateString(), 'vue' => request('vue'), 'shift' => request('shift')])); ?>"
               style="padding:.5rem 1rem;background:var(--p-red-bg);color:var(--p-red);border-radius:6px;text-decoration:none;font-weight:500;">
                ✕ Reset
            </a>
            <?php endif; ?>
        </form>
    </div>

    
    <div class="shift-legend-bar">
        <span class="shift-legend-title">Shift :</span>

        <div class="shift-legend-item normal">
            <div class="shift-legend-dot"></div>
            Shift normal
            <?php if(($stats['shift_normal'] ?? 0) > 0): ?>
                <strong style="margin-left:2px;">(<?php echo e($stats['shift_normal']); ?>)</strong>
            <?php endif; ?>
        </div>

        <div class="shift-legend-item garde">
            <div class="shift-legend-dot"></div>
            Garde
            <?php if(($stats['shift_garde'] ?? 0) > 0): ?>
                <strong style="margin-left:2px;">(<?php echo e($stats['shift_garde']); ?>)</strong>
            <?php endif; ?>
        </div>

        
        <div class="shift-filter-pills">
            <a href="<?php echo e(route('pointage.index', array_merge(request()->except('shift'), ['date' => $currentDate->toDateString()]))); ?>"
               class="shift-filter-pill all <?php echo e(!request('shift') ? 'active' : ''); ?>">
               Tous
            </a>
            <a href="<?php echo e(route('pointage.index', array_merge(request()->except('shift'), ['date' => $currentDate->toDateString(), 'shift' => 'normal']))); ?>"
               class="shift-filter-pill normal <?php echo e(request('shift') === 'normal' ? 'active' : ''); ?>">
               ● Shift normal
            </a>
            <a href="<?php echo e(route('pointage.index', array_merge(request()->except('shift'), ['date' => $currentDate->toDateString(), 'shift' => 'garde']))); ?>"
               class="shift-filter-pill garde <?php echo e(request('shift') === 'garde' ? 'active' : ''); ?>">
               ● Garde
            </a>
        </div>
    </div>

    
    <?php if(isset($geoAlerts) && $geoAlerts->count() > 0): ?>
    <?php $siteName = $geoAlerts->first()['site_name'] ?? 'la localisation du site'; ?>

    
    <div class="geo-alert-banner" onclick="openGeoAlertModal()" title="Cliquer pour voir le détail">
        <div class="geo-alert-banner-inner">
            <div style="font-size:18px;line-height:1;flex-shrink:0;">⚠️</div>
            <div class="geo-alert-title">
                Alerte localisation :
                <span class="geo-alert-count-badge"><?php echo e($geoAlerts->count()); ?></span>
                employé(s) pointé(s) en dehors de <?php echo e($siteName); ?>

            </div>
            <div class="geo-alert-caret">
                Voir le détail &nbsp;›
            </div>
        </div>
    </div>

    
    <div class="geo-modal-overlay" id="geoAlertModal">
        <div class="geo-modal">
            <div class="geo-modal-header">
                <h2>⚠️ <?php echo e($geoAlerts->count()); ?> employé(s) hors localisation</h2>
                <button class="geo-modal-close" onclick="closeGeoAlertModal()">✕</button>
            </div>
            <div class="geo-modal-body">
                <?php $__currentLoopData = $geoAlerts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $alert): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php
                    $g        = $alert['geo'];
                    $mapsUrl  = $g ? 'https://www.google.com/maps?q='.$g['latitude'].','.$g['longitude'] : null;
                    $accuracy = $g['accuracy'] ?? null;
                    $address  = $g['address'] ?? null;
                    $time     = $g['recorded_at'] ?? null;
                    $dist     = $alert['geo_distance'] ?? 0;
                    $distColor = $dist > 2000 ? '#7f1d1d' : ($dist > 500 ? '#dc2626' : '#f97316');
                ?>
                <div class="geo-modal-row">
                    <div class="geo-modal-avatar"><?php echo e($alert['avatar']); ?></div>
                    <div style="flex:1;min-width:0;">
                        <div class="geo-modal-emp-name">
                            <?php echo e($alert['nom']); ?>

                            <?php if($alert['shift_type'] === 'garde'): ?>
                                <span style="font-size:10px;background:#faf5ff;color:#7c3aed;border:1px solid #e9d5ff;padding:1px 7px;border-radius:99px;font-weight:700;margin-left:6px;">Garde</span>
                            <?php else: ?>
                                <span style="font-size:10px;background:#f0fdf4;color:#15803d;border:1px solid #bbf7d0;padding:1px 7px;border-radius:99px;font-weight:700;margin-left:6px;">Shift normal</span>
                            <?php endif; ?>
                        </div>
                        <div class="geo-modal-emp-sub">
                            <?php if($address): ?> 📍 <?php echo e($address); ?> <?php endif; ?>
                            <?php if($time): ?> &nbsp;·&nbsp; Badgé à <?php echo e($time); ?> <?php endif; ?>
                            <?php if($accuracy): ?> &nbsp;·&nbsp; Précision GPS ± <?php echo e($accuracy); ?> m <?php endif; ?>
                        </div>
                        <?php if($mapsUrl): ?>
                        <a href="<?php echo e($mapsUrl); ?>" target="_blank" class="geo-modal-maps">
                            🗺 Voir sur Google Maps
                        </a>
                        <?php endif; ?>
                    </div>
                    <div class="geo-modal-dist">
                        <div class="geo-modal-dist-val" style="color:<?php echo e($distColor); ?>"><?php echo e(number_format($dist, 0, ',', ' ')); ?> m</div>
                        <div class="geo-modal-dist-label">du site</div>
                    </div>
                </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
            <div class="geo-modal-footer">
                <span>Site de référence : <strong><?php echo e($siteName); ?></strong></span>
                <span><?php echo e(now()->setTimezone('Africa/Casablanca')->format('d/m/Y H:i')); ?></span>
            </div>
        </div>
    </div>
    <?php endif; ?>

    
    <div class="geo-modal-overlay" id="photoModal">
        <div class="geo-modal" style="max-width:460px;">
            <div class="geo-modal-header photo-modal-header">
                <h2 class="photo-modal-header" id="photoModalTitle"> Dernière photo</h2>
                <button class="geo-modal-close photo-modal-close" onclick="closePhotoModal()">✕</button>
            </div>
            <div class="geo-modal-body photo-modal-body" id="photoModalBody">
                <div class="photo-modal-placeholder">Chargement…</div>
            </div>
            <div class="geo-modal-footer">
                <span id="photoModalMeta"></span>
            </div>
        </div>
    </div>

    <div class="pt-weeknav">
        <?php
            $prevDate     = $currentDate->copy()->subWeek();
            $nextDate     = $currentDate->copy()->addWeek();
            $filterParams = request()->only(['search','department','shift']);
        ?>
        <a href="<?php echo e(route('pointage.index', array_merge($filterParams, ['date' => $prevDate->toDateString()]))); ?>" class="pt-weeknav-btn">&#8249;</a>
        <span class="pt-week-label"><?php echo e($startOfWeek->translatedFormat('d M')); ?> – <?php echo e($endOfWeek->translatedFormat('d M Y')); ?></span>
        <span class="pt-week-badge">Semaine <?php echo e($currentDate->weekOfYear); ?></span>
        <a href="<?php echo e(route('pointage.index', array_merge($filterParams, ['date' => $nextDate->toDateString()]))); ?>" class="pt-weeknav-btn">&#8250;</a>
        <a href="<?php echo e(route('pointage.index', array_merge($filterParams, ['date' => today()->toDateString()]))); ?>"
           class="pt-weeknav-btn" style="font-size:11px;width:auto;padding:0 10px;">Aujourd'hui</a>
    </div>

    
    <div class="pt-body">

        
        <div class="pt-days">
            <?php $__currentLoopData = $weekDays; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $day): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <a href="<?php echo e(route('pointage.index', array_merge($filterParams, ['date' => $day['date']->toDateString()]))); ?>"
               class="pt-day <?php echo e($day['isSelected'] ? 'active' : ''); ?>">
                <div style="flex:1;min-width:0;">
                    <div class="pt-day-name"><?php echo e($day['label']); ?></div>
                    <div class="pt-day-date"><?php echo e($day['short']); ?></div>
                    <?php if($day['valide'] && $day['validated_by']): ?>
                        <div class="pt-day-validator">✓ <?php echo e($day['validated_by']); ?></div>
                        <?php if($day['validated_at']): ?>
                        <div class="pt-day-validator-time"><?php echo e($day['validated_at']); ?></div>
                        <?php endif; ?>
                    <?php elseif(!$day['valide']): ?>
                        <div style="font-size:9px;color:var(--p-text-light);margin-top:3px;font-style:italic;">Non validé</div>
                    <?php endif; ?>
                </div>
                <div class="pt-day-check <?php echo e($day['valide'] ? 'ok' : 'pending'); ?>">
                    <?php echo e($day['valide'] ? '✓' : '○'); ?>

                </div>
            </a>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>

        
        <div class="pt-table-wrap">
            <table class="pt-table">
                <thead>
                    <tr>
                        <th style="width:44px">Validé</th>
                        <th style="width:32px" title="Géolocalisation GPS">GPS</th>
                        <th>Employé</th>
                        <th style="width:80px">Absence</th>
                        <th>Début / Fin shift</th>
                        <th>Pause total</th>
                        <th>Pause début / fin</th>
                        <th style="width:80px">Total travaillé</th>
                        <th>Photo</th>
                    </tr>
                </thead>
                <tbody>
                <?php $__currentLoopData = $employees; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $emp): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php
                    $p          = $emp['pointage'];
                    $statut     = $p?->statut ?? 'pas_de_badge';
                    $valide     = $p?->valide ?? false;
                    $isDimmed   = $p && $p->total_heures && $p->total_heures < 1;
                    $isAbsent   = in_array($statut, ['absent', 'absence_injustifiee']);
                    $isNoBadge  = $statut === 'pas_de_badge' && !$p?->heure_entree;
                    $isMidnight = $p?->heure_sortie === '00:00:00' || $p?->heure_sortie === '00:00';
                    $geo        = $emp['geo'] ?? null;
                    $hasGeo     = $geo && !($geo['denied'] ?? true) && isset($geo['latitude'], $geo['longitude']);
                    $mapsUrl    = $hasGeo ? 'https://www.google.com/maps?q='.$geo['latitude'].','.$geo['longitude'] : null;
                    $isGeoAlert = $emp['geo_alert'] ?? false;

                    // Shift type résolu
                    $shiftType  = $emp['shift_type'] ?? 'normal';
                    $isGarde    = $shiftType === 'garde';
                    $rowClass   = $isGarde ? 'row-shift-garde' : 'row-shift-normal';
                ?>
                <tr class="<?php echo e($isDimmed ? 'pt-row-dimmed' : ''); ?> <?php echo e($rowClass); ?> <?php echo e($isGeoAlert ? 'row-geo-alert' : ''); ?>"
                    id="row-emp-<?php echo e($emp['id']); ?>">

                    
                    <td>
                        <?php if($p): ?>
                        <button class="pt-check <?php echo e($valide ? 'ok' : 'pending'); ?>"
                                data-id="<?php echo e($p->id); ?>"
                                data-url="<?php echo e(route('pointage.toggle-valider', $p->id)); ?>"
                                onclick="toggleValider(this)"
                                title="<?php echo e($valide ? 'Validé – cliquer pour annuler' : 'Cliquer pour valider'); ?>">
                            <?php echo e($valide ? '✓' : '○'); ?>

                        </button>
                        <?php else: ?>
                        <div class="pt-check pending">○</div>
                        <?php endif; ?>
                    </td>

                    
                    <td style="text-align:center;padding:10px 6px;">
                        <?php if($hasGeo): ?>
<div class="geo-tooltip-wrap">
    <a href="<?php echo e($mapsUrl); ?>" target="_blank"
       style="font-size:18px;text-decoration:none;cursor:pointer;position:relative;z-index:1000;<?php echo e($isGeoAlert ? 'filter:drop-shadow(0 0 2px #dc2626);' : ''); ?>">
       <?php echo e($isGeoAlert ? '📍' : '📍'); ?>

    </a>
    <div class="geo-tooltip" style="pointer-events:none;">
                                    <?php if(!empty($geo['address'])): ?>
                                    <div style="font-weight:600;margin-bottom:8px;color:#e2e8f0;font-size:12px;max-width:220px;white-space:normal;line-height:1.4;">
                                        <?php echo e($geo['address']); ?>

                                    </div>
                                    <?php endif; ?>
                                    <div class="geo-tooltip-row">
                                        <span class="geo-tooltip-label">Latitude</span>
                                        <span class="geo-tooltip-val"><?php echo e(number_format($geo['latitude'], 5)); ?>°</span>
                                    </div>
                                    <div class="geo-tooltip-row">
                                        <span class="geo-tooltip-label">Longitude</span>
                                        <span class="geo-tooltip-val"><?php echo e(($geo['longitude'] < 0 ? '−' : '')); ?><?php echo e(number_format(abs($geo['longitude']), 5)); ?>°</span>
                                    </div>
                                    <?php if(!empty($geo['accuracy'])): ?>
                                    <div class="geo-tooltip-row">
                                        <span class="geo-tooltip-label">Précision</span>
                                        <span class="geo-tooltip-val" style="color:<?php echo e($geo['accuracy'] <= 30 ? '#86efac' : '#fde68a'); ?>">
                                            ± <?php echo e($geo['accuracy']); ?> m
                                        </span>
                                    </div>
                                    <?php endif; ?>
                                    <?php if(!empty($geo['recorded_at'])): ?>
                                    <div class="geo-tooltip-row">
                                        <span class="geo-tooltip-label">À</span>
                                        <span class="geo-tooltip-val"><?php echo e($geo['recorded_at']); ?></span>
                                    </div>
                                    <?php endif; ?>
                                    <?php if($isGeoAlert): ?>
                                    <div class="geo-tooltip-alert">
                                        ⚠️ À <?php echo e($emp['geo_distance']); ?> m de <?php echo e($emp['site_name'] ?? 'la localisation du site'); ?>

                                    </div>
                                    <?php endif; ?>
                                    <a href="<?php echo e($mapsUrl); ?>" target="_blank" class="geo-maps-link">🗺 Voir sur Google Maps →</a>
                                </div>
                            </div>
                        <?php elseif($p && $p->heure_entree): ?>
                            <span style="font-size:16px;opacity:.3;" title="Géolocalisation non disponible">📍</span>
                        <?php else: ?>
                            <span style="color:var(--p-text-light);font-size:12px;">—</span>
                        <?php endif; ?>
                    </td>

                    
                    <td>
                        <div style="display:flex;align-items:center;gap:8px;">
                            <div class="pt-avatar <?php echo e($isGarde ? 'garde' : 'normal'); ?>">
                                <?php echo e($emp['avatar']); ?>

                            </div>
                            <div>
                                <div style="font-size:13px;font-weight:500;color:var(--p-text);display:flex;align-items:center;gap:6px;">
                                    <?php echo e($emp['nom']); ?>

                                    <?php if($isGeoAlert): ?>
                                    <span title="Pointage en dehors de la localisation du site" style="font-size:11px;">⚠️</span>
                                    <?php endif; ?>
                                </div>
                                <div class="shift-type-pill <?php echo e($isGarde ? 'garde' : 'normal'); ?>">
                                    <span class="pill-dot"></span>
                                    <?php echo e($isGarde ? 'Garde' : 'Shift normal'); ?>

                                </div>
                            </div>
                        </div>
                    </td>

                    
                    <td>
                        <div style="display:flex;align-items:center;gap:8px;">
                            <input type="checkbox"
                                   class="absent-checkbox"
                                   data-employee="<?php echo e($emp['id']); ?>"
                                   data-date="<?php echo e($currentDate->toDateString()); ?>"
                                   data-url="<?php echo e(route('pointage.toggle-absence')); ?>"
                                   <?php echo e($isAbsent ? 'checked' : ''); ?>

                                   onchange="toggleAbsence(this)">
                            <span class="pt-badge pt-badge-absent"
                                  id="badge-absent-<?php echo e($emp['id']); ?>"
                                  style="<?php echo e(!$isAbsent ? 'display:none;' : ''); ?>">
                                Absent
                            </span>
                        </div>
                    </td>

                    
                    <td>
                        <?php if($p && $p->heure_entree): ?>
                        <div style="display:flex;align-items:center;gap:4px;">
                            <span class="pt-time-pill pt-pill-start">
                                <?php echo e(\Carbon\Carbon::parse($p->heure_entree)->format('H:i')); ?>

                            </span>
                            <span class="pt-time-sep">–</span>
                            <?php if($p->heure_sortie): ?>
                            <span class="pt-time-pill <?php echo e($isMidnight ? 'pt-pill-midnight' : 'pt-pill-end'); ?>">
                                <?php echo e(\Carbon\Carbon::parse($p->heure_sortie)->format('H:i')); ?><?php echo e($isMidnight ? '*' : ''); ?>

                            </span>
                            <?php else: ?>
                            <span style="font-size:11px;color:var(--p-text-light)">En cours…</span>
                            <?php endif; ?>
                        </div>
                        <?php elseif($isAbsent): ?>
                        <span style="color:var(--p-text-light)">—</span>
                        <?php elseif($isNoBadge): ?>
                        <span class="pt-badge pt-badge-nobadge">Pas de badge</span>
                        <?php else: ?>
                        <span style="color:var(--p-text-light)">—</span>
                        <?php endif; ?>
                    </td>

                    
                    <td>
                        <?php if($p && !$isAbsent && !$isNoBadge): ?>
                        <span class="pt-pause <?php echo e($p->pause_minutes > 0 ? 'pt-pause-on' : 'pt-pause-off'); ?>">
                            <?php echo e($p->pause_formatee); ?>

                        </span>
                        <?php else: ?>
                        <span style="color:var(--p-text-light)">—</span>
                        <?php endif; ?>
                    </td>

                    
                    <td>
                        <?php if($p && $p->pause_debut && $p->pause_fin): ?>
                        <span class="pt-time-pill pt-pill-start"><?php echo e($p->pause_debut); ?></span>
                        <span class="pt-time-sep">–</span>
                        <span class="pt-time-pill pt-pill-end"><?php echo e($p->pause_fin); ?></span>
                        <?php elseif($p?->pause_debut): ?>
                        <span class="pt-time-pill pt-pill-start"><?php echo e($p->pause_debut); ?></span>
                        <span style="color:var(--p-text-light);font-size:11px;">en cours</span>
                        <?php else: ?>
                        <span style="color:var(--p-text-light)">—</span>
                        <?php endif; ?>
                    </td>

                    
                    <td>
                        <?php if($p && $p->total_heures): ?>
                        <span class="pt-total <?php echo e($p->total_heures > 10 ? 'long' : ''); ?>">
                            <?php echo e($p->total_heures_formate); ?>

                        </span>
                        <?php else: ?>
                        <span style="color:var(--p-border)">—</span>
                        <?php endif; ?>
                    </td>

                    
                    <td>
                        <button class="pt-action-btn"
                                data-url="<?php echo e(route('pointage.last-photo', $emp['id'])); ?>"
                                data-name="<?php echo e($emp['nom']); ?>"
                                onclick="showLastPhoto(this)">
                            Voir photo
                        </button>
                    </td>

                </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </tbody>
            </table>
        </div>
    </div>

    
    <div class="pt-statusbar">
        <div style="display:flex;gap:1.5rem;flex-wrap:wrap;align-items:center;">
            <div class="pt-stat">
                <div class="pt-stat-dot" style="background:var(--p-teal)"></div>
                Validés : <strong style="color:var(--p-teal)"><?php echo e($stats['valides']); ?></strong>
            </div>
            <div class="pt-stat">
                <div class="pt-stat-dot" style="background:var(--p-amber)"></div>
                En attente : <strong style="color:var(--p-amber)"><?php echo e($stats['en_attente']); ?></strong>
            </div>
            <div class="pt-stat">
                <div class="pt-stat-dot" style="background:var(--p-red)"></div>
                Absents : <strong style="color:var(--p-red)"><?php echo e($stats['absents']); ?></strong>
            </div>
            <div class="pt-stat">Total : <strong><?php echo e($stats['total']); ?></strong></div>
            <div class="pt-stat">
                <div class="pt-stat-dot" style="background:var(--p-teal)"></div>
                GPS actifs : <strong style="color:var(--p-teal)"><?php echo e($stats['geo_ok'] ?? 0); ?></strong>
            </div>
            <div class="pt-stat">
                <div class="pt-stat-dot" style="background:#dc2626"></div>
                Alertes localisation : <strong style="color:#dc2626"><?php echo e($stats['geo_alerts'] ?? 0); ?></strong>
            </div>

            
            <div class="pt-stat" style="border-left:1px solid var(--p-border);padding-left:1.5rem;margin-left:.5rem;">
                <div class="pt-stat-dot" style="background:var(--shift-normal-dot)"></div>
                Shift : <strong style="color:var(--shift-normal-text)"><?php echo e($stats['shift_normal'] ?? 0); ?></strong>
            </div>
            <div class="pt-stat">
                <div class="pt-stat-dot" style="background:var(--shift-garde-dot)"></div>
                Garde : <strong style="color:var(--shift-garde-text)"><?php echo e($stats['shift_garde'] ?? 0); ?></strong>
            </div>
        </div>
    </div>

</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
const CSRF = document.querySelector('meta[name="csrf-token"]').content;

// ── Tooltip géoloc en position fixed (évite le clipping / l'affichage en arrière-plan dans le tableau scrollable) ──
document.querySelectorAll('.geo-tooltip-wrap').forEach(function (wrap) {
    var tooltip = wrap.querySelector('.geo-tooltip');
    if (!tooltip) return;

    // On détache le tooltip du tableau et on l'attache directement à <body>.
    // Raison : si un ancêtre du tableau (layout, conteneur "zoom"/scale, etc.)
    // a une propriété transform/filter/perspective, il devient le "containing block"
    // des éléments position:fixed qu'il contient — ce qui fausse tout le calcul
    // basé sur les coordonnées viewport (getBoundingClientRect). En rattachant le
    // tooltip à body (qui n'a jamais ce genre d'ancêtre), le fixed redevient
    // relatif à la fenêtre, comme attendu.
    document.body.appendChild(tooltip);

    function positionTooltip() {
        var rect = wrap.getBoundingClientRect();
        tooltip.style.display = 'block';
        var tRect = tooltip.getBoundingClientRect();
        var left = rect.left + rect.width / 2;
        var top  = rect.top - 8 - tRect.height;
        // Ne pas sortir de l'écran en haut : si pas de place au-dessus, on affiche en dessous
        if (top < 4) {
            top = rect.bottom + 8;
        }
        // Ne pas sortir de l'écran sur les côtés
        var minLeft = tRect.width / 2 + 4;
        var maxLeft = window.innerWidth - tRect.width / 2 - 4;
        if (left < minLeft) left = minLeft;
        if (left > maxLeft) left = maxLeft;

        tooltip.style.left = left + 'px';
        tooltip.style.top  = top + 'px';
    }

    wrap.addEventListener('mouseenter', positionTooltip);
    wrap.addEventListener('mouseleave', function () {
        tooltip.style.display = 'none';
    });
});

const pdfDropdown = document.querySelector('.pt-pdf-dropdown');
document.getElementById('btn-pdf-toggle').addEventListener('click', function (e) {
    e.stopPropagation();
    pdfDropdown.classList.toggle('open');
});
document.addEventListener('click', () => pdfDropdown.classList.remove('open'));

// ── Valider la journée complète ──────────────────────────────────────────────
document.getElementById('btn-validate').addEventListener('click', async function () {
    const btn = this;
    btn.disabled = true; btn.textContent = '…';
    try {
        const res  = await fetch(btn.dataset.url, {
            method: 'POST',
            headers: {'X-CSRF-TOKEN': CSRF, 'Content-Type': 'application/json', 'Accept': 'application/json'},
            body: JSON.stringify({date: btn.dataset.date}),
        });
        const data = await res.json();
        if (data.success) {
            btn.textContent = '✓ ' + data.message;
            btn.style.background = '#0f766e';
            updateDaySidebarValidation(data.validator, data.validated_at);
            setTimeout(() => { btn.textContent = '✓ Valider la journée'; btn.style.background = ''; btn.disabled = false; }, 3000);
        } else {
            btn.textContent = 'Erreur !'; btn.style.background = '#dc2626'; btn.disabled = false;
        }
    } catch(e) {
        btn.textContent = 'Erreur !'; btn.style.background = '#dc2626'; btn.disabled = false;
    }
});

function updateDaySidebarValidation(validatorName, validatedAt) {
    const activeDay = document.querySelector('.pt-day.active');
    if (!activeDay) return;
    const check = activeDay.querySelector('.pt-day-check');
    if (check) { check.className = 'pt-day-check ok'; check.textContent = '✓'; }
    const dayInfo = activeDay.querySelector('div[style]') ?? activeDay.querySelector('div');
    if (dayInfo) {
        let el = dayInfo.querySelector('.pt-day-validator');
        if (!el) { el = document.createElement('div'); el.className = 'pt-day-validator'; dayInfo.appendChild(el); }
        el.textContent = '✓ ' + (validatorName || '');
        let tel = dayInfo.querySelector('.pt-day-validator-time');
        if (!tel && validatedAt) { tel = document.createElement('div'); tel.className = 'pt-day-validator-time'; dayInfo.appendChild(tel); }
        if (tel && validatedAt) tel.textContent = validatedAt;
        const nv = dayInfo.querySelector('div[style*="italic"]');
        if (nv) nv.remove();
    }
    activeDay.style.borderLeftColor = '#0d9488';
}

// ── Toggle valider ───────────────────────────────────────────────────────────
async function toggleValider(btn) {
    try {
        const res  = await fetch(btn.dataset.url, { method: 'POST', headers: {'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json'} });
        const data = await res.json();
        btn.classList.toggle('ok', data.valide);
        btn.classList.toggle('pending', !data.valide);
        btn.textContent = data.valide ? '✓' : '○';
    } catch(e) { console.error(e); }
}

// ── Toggle absence ───────────────────────────────────────────────────────────
async function toggleAbsence(checkbox) {
    const empId = checkbox.dataset.employee, date = checkbox.dataset.date;
    const url = checkbox.dataset.url, isAbsent = checkbox.checked;
    const badge = document.getElementById('badge-absent-' + empId);
    checkbox.disabled = true;
    try {
        const res = await fetch(url, {
            method: 'POST',
            headers: {'X-CSRF-TOKEN': CSRF, 'Content-Type': 'application/json', 'Accept': 'application/json'},
            body: JSON.stringify({employee_id: empId, date, absent: isAbsent}),
        });
        if (!res.ok) { checkbox.checked = !isAbsent; return; }
        const data = await res.json();
        if (data.success) {
            if (badge) badge.style.display = isAbsent ? 'inline-block' : 'none';
        } else {
            checkbox.checked = !isAbsent;
        }
    } catch(e) {
        console.error(e); checkbox.checked = !isAbsent;
    } finally {
        checkbox.disabled = false;
    }
}


// ── Modal alerte géolocalisation ─────────────────────────────────────────────
function openGeoAlertModal() {
    var modal = document.getElementById('geoAlertModal');
    if (modal) {
        modal.classList.add('open');
        document.body.style.overflow = 'hidden';
    }
}

function closeGeoAlertModal() {
    var modal = document.getElementById('geoAlertModal');
    if (modal) {
        modal.classList.remove('open');
        document.body.style.overflow = '';
    }
}

// Fermer en cliquant sur l'overlay
var geoOverlay = document.getElementById('geoAlertModal');
if (geoOverlay) {
    geoOverlay.addEventListener('click', function (e) {
        if (e.target === geoOverlay) closeGeoAlertModal();
    });
}

// ── Modal photo — dernière photo badgeuse ────────────────────────────────────
async function showLastPhoto(btn) {
    var modal = document.getElementById('photoModal');
    var body  = document.getElementById('photoModalBody');
    var title = document.getElementById('photoModalTitle');
    var meta  = document.getElementById('photoModalMeta');

    title.textContent = '' + (btn.dataset.name || 'Photo');
    body.innerHTML     = '<div class="photo-modal-placeholder">Chargement…</div>';
    meta.textContent   = '';

    modal.classList.add('open');
    document.body.style.overflow = 'hidden';

    try {
        const res  = await fetch(btn.dataset.url, {
            headers: {'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json'},
        });
        const data = await res.json();

        if (data.success && data.photo_url) {
            body.innerHTML = '<img src="' + data.photo_url + '" alt="Photo badgeuse" class="photo-modal-img">';
            var typeLabel = data.type === 'entree' ? 'Entrée' : (data.type === 'sortie' ? 'Sortie' : (data.type || ''));
            meta.textContent = (typeLabel ? typeLabel + ' — ' : '') + (data.recorded_at || '');
        } else {
            body.innerHTML = '<div class="photo-modal-placeholder">' + (data.message || 'Aucune photo disponible pour cet employé.') + '</div>';
        }
    } catch (e) {
        body.innerHTML = '<div class="photo-modal-placeholder" style="color:var(--p-red);">Erreur lors du chargement de la photo.</div>';
    }
}

function closePhotoModal() {
    var modal = document.getElementById('photoModal');
    if (modal) {
        modal.classList.remove('open');
        document.body.style.overflow = '';
    }
}

var photoOverlay = document.getElementById('photoModal');
if (photoOverlay) {
    photoOverlay.addEventListener('click', function (e) {
        if (e.target === photoOverlay) closePhotoModal();
    });
}

// Fermer avec Escape (géoloc + photo)
document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') {
        closeGeoAlertModal();
        closePhotoModal();
    }
});
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\Projects\SIRH-\resources\views/pointage/index.blade.php ENDPATH**/ ?>