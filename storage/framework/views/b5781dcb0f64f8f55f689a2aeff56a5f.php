<?php $__env->startSection('title', 'Pointage — Badgeuse'); ?>

<?php $__env->startPush('styles'); ?>
<style>
    /* ── Variables thème clair ──────────────────────────── */
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
    }

    .pointage-wrap { display: flex; flex-direction: column; height: calc(100vh - 64px); background: var(--p-bg); }

    /* ── Topbar ─────────────────────────────────────────── */
    .pt-topbar {
        background: var(--p-surface);
        border-bottom: 1px solid var(--p-border);
        padding: 0 1.5rem;
        height: 52px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-shrink: 0;
    }
    .pt-topbar-left { display: flex; align-items: center; gap: 1rem; }
    .pt-title { font-size: 15px; font-weight: 600; color: var(--p-text); }
    .pt-tabs { display: flex; background: var(--p-bg); border: 1px solid var(--p-border); border-radius: 8px; overflow: hidden; }
    .pt-tab {
        padding: 5px 16px; font-size: 12px; font-weight: 500; cursor: pointer;
        color: var(--p-text-muted); background: transparent; border: none; transition: all .15s;
        text-decoration: none; display: flex; align-items: center;
    }
    .pt-tab.active, .pt-tab:hover { background: var(--p-teal); color: #fff; }
    .pt-topbar-right { display: flex; align-items: center; gap: .75rem; }
    .pt-sync {
        display: flex; align-items: center; gap: 6px;
        font-size: 11px; color: var(--p-text-muted);
        background: var(--p-bg); border: 1px solid var(--p-border);
        padding: 4px 10px; border-radius: 20px;
    }
    .pt-sync-dot { width: 7px; height: 7px; border-radius: 50%; background: var(--p-teal); animation: blink 2s infinite; }
    @keyframes blink { 0%,100%{opacity:1} 50%{opacity:.3} }
    .pt-btn-validate {
        background: var(--p-teal); color: #fff; border: none;
        padding: 7px 16px; border-radius: 8px; font-size: 13px; font-weight: 600;
        cursor: pointer; transition: background .15s;
    }
    .pt-btn-validate:hover { background: #0f766e; }

    /* ── Week nav ───────────────────────────────────────── */
    .pt-weeknav {
        background: var(--p-surface); border-bottom: 1px solid var(--p-border);
        padding: .75rem 1.5rem; display: flex; align-items: center; gap: .75rem; flex-shrink: 0;
    }
    .pt-weeknav-btn {
        background: var(--p-bg); border: 1px solid var(--p-border);
        width: 28px; height: 28px; border-radius: 6px; cursor: pointer;
        display: flex; align-items: center; justify-content: center;
        font-size: 15px; color: var(--p-text-muted); transition: all .15s;
    }
    .pt-weeknav-btn:hover { border-color: var(--p-teal); color: var(--p-teal); }
    .pt-week-label { font-size: 13px; font-weight: 500; color: var(--p-text); }
    .pt-week-badge {
        background: var(--p-teal-light); color: var(--p-teal);
        font-size: 11px; font-weight: 600; padding: 2px 10px; border-radius: 20px;
    }

    /* ── Body (day sidebar + table) ─────────────────────── */
    .pt-body { display: flex; flex: 1; overflow: hidden; }

    /* Day sidebar */
    .pt-days {
        width: 155px; flex-shrink: 0; background: var(--p-surface);
        border-right: 1px solid var(--p-border); overflow-y: auto;
    }
    .pt-day {
        display: flex; align-items: center; justify-content: space-between;
        padding: 11px 14px; cursor: pointer; border-left: 3px solid transparent;
        text-decoration: none; transition: all .12s;
    }
    .pt-day:hover { background: var(--p-teal-bg); }
    .pt-day.active { background: var(--p-teal-bg); border-left-color: var(--p-teal); }
    .pt-day-name { font-size: 12px; font-weight: 600; color: var(--p-text); }
    .pt-day-date { font-size: 11px; color: var(--p-text-muted); margin-top: 1px; }
    .pt-day-check {
        width: 20px; height: 20px; border-radius: 50%; flex-shrink: 0;
        display: flex; align-items: center; justify-content: center; font-size: 10px;
    }
    .pt-day-check.ok { background: var(--p-teal); color: #fff; }
    .pt-day-check.pending { border: 1.5px solid var(--p-border); color: var(--p-text-light); }

    /* Table */
    .pt-table-wrap { flex: 1; overflow: auto; }
    .pt-table { width: 100%; border-collapse: collapse; min-width: 860px; }
    .pt-table thead th {
        position: sticky; top: 0; z-index: 2;
        background: var(--p-gray-bg); border-bottom: 1px solid var(--p-border);
        padding: 10px 12px; text-align: left; white-space: nowrap;
        font-size: 11px; font-weight: 600; text-transform: uppercase;
        letter-spacing: .05em; color: var(--p-text-muted);
    }
    .pt-table td { padding: 10px 12px; border-bottom: 1px solid var(--p-border-soft); vertical-align: middle; }
    .pt-table tbody tr:hover td { background: var(--p-teal-bg); }

    /* Badges heures */
    .pt-time-pill {
        display: inline-block; padding: 3px 9px; border-radius: 6px;
        font-size: 12px; font-weight: 700; letter-spacing: .02em;
    }
    .pt-pill-start  { background: var(--p-blue-bg);   color: var(--p-blue); }
    .pt-pill-end    { background: var(--p-purple-bg);  color: var(--p-purple); }
    .pt-pill-midnight { background: #ede9fe; color: #6d28d9; }
    .pt-time-sep { color: var(--p-text-light); font-size: 12px; margin: 0 2px; }

    /* Badge pause */
    .pt-pause {
        display: inline-block; padding: 3px 9px; border-radius: 6px;
        font-size: 12px; font-weight: 600;
    }
    .pt-pause-on  { background: var(--p-amber-bg); color: var(--p-amber); }
    .pt-pause-off { background: var(--p-green-bg); color: var(--p-green); }

    /* Statuts */
    .pt-badge {
        display: inline-block; padding: 3px 10px; border-radius: 6px; font-size: 11px; font-weight: 500;
    }
    .pt-badge-absent { background: var(--p-red-bg); color: var(--p-red); }
    .pt-badge-nobadge { background: var(--p-gray-bg); color: var(--p-text-muted); border: 1px solid var(--p-border); }

    /* Shift */
    .pt-shift { font-size: 12px; color: var(--p-text-muted); white-space: nowrap; }
    .pt-total { font-size: 13px; font-weight: 700; color: var(--p-teal); }
    .pt-total.long { color: var(--p-amber); }

    /* Checkbox validé */
    .pt-check {
        width: 22px; height: 22px; border-radius: 50%; display: flex;
        align-items: center; justify-content: center; cursor: pointer;
        border: none; transition: all .15s;
    }
    .pt-check.ok { background: var(--p-teal); color: #fff; }
    .pt-check.pending { background: transparent; border: 1.5px solid var(--p-border); color: var(--p-text-light); }

    /* Action buttons */
    .pt-action-btn {
        font-size: 11px; font-weight: 500; padding: 4px 10px; border-radius: 6px;
        cursor: pointer; border: 1px solid var(--p-border); background: var(--p-surface);
        color: var(--p-text-muted); transition: all .15s; white-space: nowrap;
    }
    .pt-action-btn:hover { border-color: var(--p-teal); color: var(--p-teal); }
    .pt-action-btn.keep { background: var(--p-teal-bg); border-color: var(--p-teal); color: var(--p-teal); }

    /* ── Status bar ─────────────────────────────────────── */
    .pt-statusbar {
        background: var(--p-surface); border-top: 1px solid var(--p-border);
        padding: .5rem 1.5rem; display: flex; align-items: center;
        justify-content: space-between; flex-shrink: 0;
    }
    .pt-stat { display: flex; align-items: center; gap: 6px; font-size: 12px; color: var(--p-text-muted); }
    .pt-stat-dot { width: 8px; height: 8px; border-radius: 50%; }
    .pt-stat strong { font-weight: 600; }

    /* Employee avatar */
    .pt-avatar {
        width: 30px; height: 30px; border-radius: 50%; flex-shrink: 0;
        display: flex; align-items: center; justify-content: center;
        font-size: 11px; font-weight: 700;
        background: var(--p-teal-light); color: var(--p-teal);
    }

    /* Dimmed row (ex: shift très court) */
    .pt-row-dimmed td { opacity: .55; }
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
<div class="pointage-wrap">

    
    <div class="pt-topbar">
        <div class="pt-topbar-left">
            <span class="pt-title">Pointage — Badgeuse</span>
            <div class="pt-tabs">
                <a href="<?php echo e(route('pointage.index', array_merge(request()->only(['search', 'department']), ['date' => $currentDate->toDateString(), 'vue' => 'tous']))); ?>"
                   class="pt-tab <?php echo e(($vue ?? request('vue', 'tous')) === 'tous' ? 'active' : ''); ?>">
                    Tous
                </a>
                <a href="<?php echo e(route('pointage.index', array_merge(request()->only(['search', 'department']), ['date' => $currentDate->toDateString(), 'vue' => 'pointe']))); ?>"
                   class="pt-tab <?php echo e(($vue ?? request('vue')) === 'pointe' ? 'active' : ''); ?>">
                    Pointe
                </a>
                <a href="<?php echo e(route('pointage.index', array_merge(request()->only(['search', 'department']), ['date' => $currentDate->toDateString(), 'vue' => 'non_pointe']))); ?>"
                   class="pt-tab <?php echo e(($vue ?? request('vue')) === 'non_pointe' ? 'active' : ''); ?>">
                    Non pointe
                </a>
            </div>
        </div>
        <div class="pt-topbar-right">
            <?php if($dernierSync): ?>
            <div class="pt-sync">
                <div class="pt-sync-dot"></div>
                <span>Sync tablette <strong id="sync-ago">—</strong></span>
            </div>
            <?php endif; ?>

            
            <div class="pdf-export-dropdown">
                <a href="<?php echo e(route('pointage.pdf', request()->only(['date', 'department', 'search', 'vue']))); ?>"
                   class="pt-btn-export" title="Exporter PDF (filtres actuels)"
                   style="background: var(--p-primary): #22c55e;; padding: 7px 14px; border-radius: 8px; font-size: 13px; font-weight: 600; text-decoration: none; transition: background .15s; white-space: nowrap;">
                    PDF
                </a>
            </div>
            <a href="<?php echo e(route('pointage.badges-pin')); ?>"
             style="background:#7c3aed;color:white;padding:7px 14px;border-radius:8px;font-size:13px;font-weight:600;text-decoration:none;">
            Badges PIN
            </a>

            
            <a href="<?php echo e(route('temps.vue-ensemble', ['annee' => $currentDate->year, 'mois' => $currentDate->month])); ?>"
               class="pt-btn-overview" title="Voir vue d'ensemble mensuelle"
               style="background: #3b82f6; color: white; padding: 7px 14px; border-radius: 8px; font-size: 13px; font-weight: 600; text-decoration: none; transition: background .15s;"
               target="_blank">
                 Vue Mensuelle
            </a>
        
            <button class="pt-btn-validate" id="btn-validate"
                    data-date="<?php echo e($currentDate->toDateString()); ?>"
                    data-url="<?php echo e(route('pointage.valider-journee')); ?>">
                ✓ Valider la journée
            </button>
        </div>
    </div>

    
    <div style="background: var(--p-surface); border-bottom: 1px solid var(--p-border); padding: 0.75rem 1.5rem; display: flex; gap: 0.75rem; align-items: center; font-size: 13px;">
         <strong>Filtrer:</strong> 
        <form method="GET" action="<?php echo e(route('pointage.index')); ?>" style="display: flex; gap: 0.5rem; align-items: center; flex: 1;">
            <input type="hidden" name="date" value="<?php echo e($currentDate->toDateString()); ?>">
            <input type="hidden" name="vue" value="<?php echo e($vue ?? request('vue', 'tous')); ?>">

            <input type="text" name="search" placeholder="Nom employé..." value="<?php echo e(request('search')); ?>" onchange="this.form.submit()" style="flex: 1; padding: 0.5rem; border: 1px solid var(--p-border); border-radius: 6px;">
            <select name="department" onchange="this.form.submit()" style="padding: 0.5rem; border: 1px solid var(--p-border); border-radius: 6px;">
                <option value=""> Tous départements</option>
                <?php $__currentLoopData = $departments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $dept): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <option value="<?php echo e($dept); ?>" <?php echo e(request('department') == $dept ? 'selected' : ''); ?>><?php echo e($dept); ?></option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
<?php if(request()->hasAny(['search', 'department'])): ?>
                <a href="<?php echo e(route('pointage.index', ['date' => $currentDate->toDateString(), 'vue' => request('vue')])); ?>" style="padding: 0.5rem 1rem; background: var(--p-red-bg); color: var(--p-red); border-radius: 6px; text-decoration: none; font-weight: 500;">✕ Reset</a>
            <?php endif; ?>
        </form>
    </div>

    
    <div class="pt-weeknav">

<?php
            $prevDate = $currentDate->copy()->subWeek();
            $nextDate = $currentDate->copy()->addWeek();
            $filterParams = request()->only(['search', 'department']);
        ?>
        <a href="<?php echo e(route('pointage.index', array_merge($filterParams, ['date' => $prevDate->toDateString()]))); ?>" class="pt-weeknav-btn">&#8249;</a>
        <span class="pt-week-label">
            <?php echo e($startOfWeek->translatedFormat('d M')); ?> – <?php echo e($endOfWeek->translatedFormat('d M Y')); ?>

        </span>
        <span class="pt-week-badge">Semaine <?php echo e($currentDate->weekOfYear); ?></span>
        <a href="<?php echo e(route('pointage.index', array_merge($filterParams, ['date' => $nextDate->toDateString()]))); ?>" class="pt-weeknav-btn">&#8250;</a>
        <a href="<?php echo e(route('pointage.index', array_merge($filterParams, ['date' => today()->toDateString()]))); ?>"
           class="pt-weeknav-btn" title="Aujourd'hui" style="font-size:11px;width:auto;padding:0 10px;">
            Aujourd'hui
        </a>
    </div>

    
    <div class="pt-body">

        
        <div class="pt-days">
<?php $__currentLoopData = $weekDays; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $day): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <a href="<?php echo e(route('pointage.index', array_merge($filterParams, ['date' => $day['date']->toDateString()]))); ?>"
               class="pt-day <?php echo e($day['isSelected'] ? 'active' : ''); ?>">
                <div>
                    <div class="pt-day-name"><?php echo e($day['label']); ?></div>
                    <div class="pt-day-date"><?php echo e($day['short']); ?></div>
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
                        <th>Employé</th>
                        <th>Absence</th>
                    <th>Début/Fin shift</th>
                    <th>Pause total</th>
                    <th>Pause début / fin</th>
                    <th style="width:80px">Total travaillé</th>
                    <th>Action</th>

                    </tr>
                </thead>
                <tbody>
                <?php $__currentLoopData = $employees; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $emp): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php
                    $p       = $emp['pointage'];
                    $statut  = $p?->statut ?? 'pas_de_badge';
                    $valide  = $p?->valide ?? false;
                    $isDimmed = $p && $p->total_heures && $p->total_heures < 1;
                    $isAbsent = in_array($statut, ['absent','absence']);
                    $isNoBadge = $statut === 'pas_de_badge' && !$p?->heure_entree;
                    $isMidnight = $p?->heure_sortie === '00:00:00' || $p?->heure_sortie === '00:00';
                ?>
                <tr class="<?php echo e($isDimmed ? 'pt-row-dimmed' : ''); ?>" id="row-emp-<?php echo e($emp['id']); ?>">

                    
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

                    
                    <td>
                        <div style="display:flex;align-items:center;gap:8px;">
                            <div class="pt-avatar"><?php echo e($emp['avatar']); ?></div>
                            <span style="font-size:13px;font-weight:500;color:var(--p-text);"><?php echo e($emp['nom']); ?></span>
                        </div>
                    </td>

                    
            <td>
    <input type="checkbox"
           class="absent-checkbox"
           data-employee="<?php echo e($emp['id']); ?>"
           data-date="<?php echo e($currentDate->toDateString()); ?>"
           data-url="<?php echo e(route('pointage.toggle-absence')); ?>"
           style="accent-color:var(--p-teal);width:15px;height:15px;"
           <?php echo e($isAbsent ? 'checked' : ''); ?>

           onchange="toggleAbsence(this)">

    <span class="pt-badge pt-badge-absent"
          style="<?php echo e(!$isAbsent ? 'display:none;' : ''); ?>">
        Absence 
    </span>
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
                        <span class="pt-time-pill pt-pill-start"><?php echo e($p->pause_debut); ?></span> <span style="color:var(--p-text-light);font-size:11px;">en cours</span>
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
        <div style="display:flex;gap:1.5rem;">
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
            <div class="pt-stat" style="margin-left:1rem;color:var(--p-text-muted);">
                Total employés : <strong><?php echo e($stats['total']); ?></strong>
            </div>
        </div>
        <?php if($dernierSync): ?>
        <div class="pt-stat">
            <svg width="14" height="14" viewBox="0 0 16 16" fill="none" stroke="var(--p-text-light)" stroke-width="1.5">
                <rect x="2" y="2" width="5" height="12" rx="1"/>
                <rect x="9" y="2" width="5" height="5" rx="1"/>
                <rect x="9" y="9" width="5" height="5" rx="1"/>
            </svg>
            Tablette : <strong style="color:var(--p-teal)"><?php echo e($dernierSync->nom); ?></strong>
        </div>
        <?php endif; ?>
    </div>

</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
const CSRF = document.querySelector('meta[name="csrf-token"]').content;

 /* ── Sync temps affiché ─────────────────────────────── */
<?php if($dernierSync): ?>
(function() {
    const syncedAt = new Date('<?php echo e($dernierSync->derniere_connexion?->toIso8601String()); ?>');
    function updateSyncLabel() {
        const diff = Math.floor((Date.now() - syncedAt) / 1000);
        let label;
        if (diff < 60)       label = 'à l\'instant';
        else if (diff < 3600) label = `il y a ${Math.floor(diff/60)} min`;
        else                  label = `il y a ${Math.floor(diff/3600)}h`;
        const el = document.getElementById('sync-ago');
        if (el) el.textContent = label;
    }
    updateSyncLabel();
    setInterval(updateSyncLabel, 3000000);
})();
<?php endif; ?>




/* ── Valider la journée ─────────────────────────────── */
document.getElementById('btn-validate').addEventListener('click', async function() {
    const btn  = this;
    const date = btn.dataset.date;
    const url  = btn.dataset.url;

    btn.disabled = true;
    btn.textContent = '…';

    try {
        const res  = await fetch(url, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': CSRF, 'Content-Type': 'application/json', 'Accept': 'application/json' },
            body: JSON.stringify({ date })
        });
        const data = await res.json();
        btn.textContent = '✓ ' + data.message;
        btn.style.background = '#0f766e';
        setTimeout(() => { btn.textContent = '✓ Valider la journée'; btn.style.background = ''; btn.disabled = false; }, 300000);
    } catch(e) {
        btn.textContent = 'Erreur !';
        btn.style.background = '#dc2626';
        btn.disabled = false;
    }
});

 /* ── Toggle validé ──────────────────────────────────── */
async function toggleValider(btn) {
    const url = btn.dataset.url;
    try {
        const res  = await fetch(url, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' }
        });
        const data = await res.json();
        btn.classList.toggle('ok',      data.valide);
        btn.classList.toggle('pending', !data.valide);
        btn.textContent = data.valide ? '✓' : '○';
    } catch(e) { console.error(e); }
}

/* ── Toggle ignorer/garder ──────────────────────────── */
async function toggleIgnore(btn) {
    const url = btn.dataset.url;
    try {
        const res  = await fetch(url, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' }
        });
        const data = await res.json();
        btn.classList.toggle('keep', !data.ignore_badge);
        btn.textContent = data.ignore_badge ? '⊘ Ignorer' : '👁 Garder';
    } catch(e) { console.error(e); }
}
async function toggleAbsence(checkbox) {
    const url = checkbox.dataset.url;
    const employeeId = checkbox.dataset.employee;
    const date = checkbox.dataset.date;
    const badge = checkbox.parentElement.querySelector('.pt-badge-absent');

    try {
        const res = await fetch(url, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': CSRF,
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                employee_id: employeeId,
                date: date,
                absent: checkbox.checked
            })
        });

        const data = await res.json();

        // UI update
        badge.style.display = checkbox.checked ? 'inline-block' : 'none';

    } catch (e) {
        console.error(e);
        checkbox.checked = !checkbox.checked;
    }
}

</script>
<?php $__env->stopPush(); ?>


<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\HP\SIRH-\resources\views/pointage/index.blade.php ENDPATH**/ ?>