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
    /* Shift normal — fond vert très clair + barre latérale verte */
    .pt-table tbody tr.row-shift-normal td {
        background: var(--shift-normal-row);
    }
    .pt-table tbody tr.row-shift-normal td:first-child {
        border-left: 3px solid var(--shift-normal-dot);
        padding-left: 10px;
    }
    /* Garde — fond mauve très clair + barre latérale mauve */
    .pt-table tbody tr.row-shift-garde td {
        background: var(--shift-garde-row);
    }
    .pt-table tbody tr.row-shift-garde td:first-child {
        border-left: 3px solid var(--shift-garde-dot);
        padding-left: 10px;
    }
    /* Hover */
    .pt-table tbody tr.row-shift-normal:hover td { filter: brightness(.97); }
    .pt-table tbody tr.row-shift-garde:hover td  { filter: brightness(.97); }

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

    /* ── Géoloc tooltip ── */
    .geo-tooltip-wrap { position: relative; display: inline-block; }
    .geo-tooltip {
        display: none; position: absolute; bottom: calc(100% + 8px); left: 50%;
        transform: translateX(-50%);
        background: #0f172a; color: #fff;
        border-radius: 10px; padding: 12px 16px;
        font-size: 12px; line-height: 1.5;
        white-space: nowrap; z-index: 999;
        box-shadow: 0 8px 24px rgba(0,0,0,.3); min-width: 200px;
        pointer-events: none;
    }
    .geo-tooltip::after {
        content: ''; position: absolute; top: 100%; left: 50%; transform: translateX(-50%);
        border: 6px solid transparent; border-top-color: #0f172a;
    }
    .geo-tooltip-wrap:hover .geo-tooltip { display: block; }
    .geo-tooltip-row { display: flex; justify-content: space-between; gap: 16px; margin-bottom: 4px; }
    .geo-tooltip-row:last-child { margin-bottom: 0; }
    .geo-tooltip-label { color: #94a3b8; font-size: 10px; text-transform: uppercase; letter-spacing: .06em; }
    .geo-tooltip-val   { color: #fff; font-weight: 600; font-size: 11px; }
    .geo-maps-link {
        display: block; margin-top: 8px; color: #5eead4; font-size: 10px;
        text-decoration: none; border-top: 1px solid #1e293b; padding-top: 8px;
    }
    .geo-maps-link:hover { color: #99f6e4; }
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
            <a href="<?php echo e(route('pointage.pdf', request()->only(['date','department','search','vue','shift']))); ?>"
               style="background:#e2e8f0;color:#0f172a;padding:7px 14px;border-radius:8px;font-size:13px;font-weight:600;text-decoration:none;border:1px solid var(--p-border);">
                PDF
            </a>
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
                        <th>Action</th>
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

                    // Shift type résolu
                    $shiftType  = $emp['shift_type'] ?? 'normal';
                    $isGarde    = $shiftType === 'garde';
                    $rowClass   = $isGarde ? 'row-shift-garde' : 'row-shift-normal';
                ?>
                <tr class="<?php echo e($isDimmed ? 'pt-row-dimmed' : ''); ?> <?php echo e($rowClass); ?>"
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
       style="font-size:18px;text-decoration:none;cursor:pointer;position:relative;z-index:1000;">📍</a>
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
                                <div style="font-size:13px;font-weight:500;color:var(--p-text);">
                                    <?php echo e($emp['nom']); ?>

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
                        <?php if($p): ?>
                        <button class="pt-action-btn <?php echo e($p->ignore_badge ? '' : 'keep'); ?>"
                                data-id="<?php echo e($p->id); ?>"
                                data-url="<?php echo e(route('pointage.toggle-ignore', $p->id)); ?>"
                                onclick="toggleIgnore(this)">
                            <?php echo e($p->ignore_badge ? '⊘ Ignorer' : '👁 Garder'); ?>

                        </button>
                        <?php else: ?>
                        <button class="pt-action-btn" disabled style="opacity:.4;cursor:default;">⊘ Ignorer</button>
                        <?php endif; ?>
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

// ── Toggle ignore ────────────────────────────────────────────────────────────
async function toggleIgnore(btn) {
    try {
        const res  = await fetch(btn.dataset.url, { method: 'POST', headers: {'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json'} });
        const data = await res.json();
        btn.classList.toggle('keep', !data.ignore_badge);
        btn.textContent = data.ignore_badge ? '⊘ Ignorer' : '👁 Garder';
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
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\Projects\SIRH-\resources\views/pointage/index.blade.php ENDPATH**/ ?>