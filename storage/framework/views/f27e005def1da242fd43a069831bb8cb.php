<?php $__env->startSection('title', 'Pointage — Badgeuse'); ?>

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
                <?php
                    // Résolution inline du fuseau tenant (sans dépendre d'un helper externe) :
                    // config('app.current_tenant_id') est déjà positionné par le middleware
                    // tenant sur les pages admin classiques.
                    $__tenantId = config('app.current_tenant_id');
                    $__tz = $__tenantId
                        ? (\App\Models\Tenant::where('id', $__tenantId)->value('timezone') ?: 'Africa/Casablanca')
                        : 'Africa/Casablanca';
                ?>
                <span><?php echo e(\Carbon\Carbon::now($__tz)->format('d/m/Y H:i')); ?></span>
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

                    
                    <td style="text-align:center;padding:10px 6px;">
                        <?php if($hasGeo): ?>
<div class="geo-tooltip-wrap">
    <a href="<?php echo e($mapsUrl); ?>" target="_blank"
       style="font-size:18px;text-decoration:none;cursor:pointer;position:relative;z-index:1000;<?php echo e($isGeoAlert ? 'filter:drop-shadow(0 0 2px #dc2626);' : ''); ?>">
       <?php echo e($isGeoAlert ? '🔴' : '📍'); ?>

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
            data-url="<?php echo e(route('pointage.last-photo', ['employee' => $emp['id'], 'date' => $currentDate->toDateString()])); ?>"
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

    document.body.appendChild(tooltip);

    function positionTooltip() {
        var rect = wrap.getBoundingClientRect();
        tooltip.style.display = 'block';
        var tRect = tooltip.getBoundingClientRect();
        var left = rect.left + rect.width / 2;
        var top  = rect.top - 8 - tRect.height;
        if (top < 4) {
            top = rect.bottom + 8;
        }
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
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\HP\Medstaff-second-main\resources\views/pointage/index.blade.php ENDPATH**/ ?>