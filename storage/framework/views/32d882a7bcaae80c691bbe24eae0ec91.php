<?php $__env->startSection('title', 'Vue ensemble - Temps de travail'); ?>
<?php $__env->startSection('page-title', 'Vue ensemble du temps de travail'); ?>

<?php $__env->startSection('content'); ?>
<div class="ov-wrap">


<div class="filters-bar">
    <form method="GET" action="<?php echo e(route('temps.vue-ensemble')); ?>">

        <select name="employee_id" class="fb-select" onchange="this.form.submit()">
            <option value="">Selectionner un employe</option>
            <?php $__currentLoopData = $listeEmployesSelect; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $emp): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <option value="<?php echo e($emp->id); ?>" <?php echo e(($employeeId ?? '') == $emp->id ? 'selected' : ''); ?>>
                    <?php echo e($emp->first_name); ?> <?php echo e($emp->last_name); ?><?php echo e($emp->matricule ? ' — '.$emp->matricule : ''); ?>

                </option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </select>

        <select name="department" class="fb-select" style="min-width:160px;" onchange="this.form.submit()">
            <option value="">Tous les departements</option>
            <?php $__currentLoopData = $departments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $dept): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <option value="<?php echo e($dept); ?>" <?php echo e(($department ?? '') == $dept ? 'selected' : ''); ?>><?php echo e($dept); ?></option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </select>

        <input type="number" name="annee" value="<?php echo e($annee); ?>" min="2020" max="2030" class="fb-input fb-input-year">
        <input type="hidden" name="mois" value="<?php echo e($mois); ?>">

        <div class="filter-sep"></div>
        <span class="filter-label">Période</span>

        
        <input type="date" name="date_debut"
               value="<?php echo e($dateDebut ?? \Carbon\Carbon::create($annee, $mois, 1)->startOfMonth()->format('Y-m-d')); ?>"
               class="fb-input fb-input-date"
               title="Date de début">
        <span style="color:var(--slate-400);font-size:13px;">→</span>
        <input type="date" name="date_fin"
               value="<?php echo e($dateFin ?? \Carbon\Carbon::create($annee, $mois, 1)->endOfMonth()->format('Y-m-d')); ?>"
               class="fb-input fb-input-date"
               title="Date de fin">

        <button type="submit" class="btn-filter">Rechercher</button>

        
        <?php if($dateDebut && $dateFin): ?>
        <div class="filter-active-badge">
             <?php echo e(\Carbon\Carbon::parse($dateDebut)->format('d/m/Y')); ?> → <?php echo e(\Carbon\Carbon::parse($dateFin)->format('d/m/Y')); ?>

            <a href="<?php echo e(route('temps.vue-ensemble', array_filter(['employee_id' => $employeeId, 'department' => $department, 'annee' => $annee, 'mois' => $mois]))); ?>"
               title="Réinitialiser la période">✕</a>
        </div>
        <?php endif; ?>
    </form>

    
    <div class="period-nav">
        <a href="<?php echo e(route('temps.vue-ensemble', ['mois' => $moisPrecedent->month, 'annee' => $moisPrecedent->year, 'employee_id' => $employeeId ?? '', 'department' => $department ?? ''])); ?>">&larr;</a>
        <span class="period-label"><?php echo e(\Carbon\Carbon::create($annee, $mois, 1)->locale('fr')->translatedFormat('F Y')); ?></span>
        <a href="<?php echo e(route('temps.vue-ensemble', ['mois' => $moisSuivant->month, 'annee' => $moisSuivant->year, 'employee_id' => $employeeId ?? '', 'department' => $department ?? ''])); ?>">&rarr;</a>

        <button class="cal-popup-btn" id="calPopupBtn" title="Voir les jours planifies">
            <svg width="15" height="15" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                <rect x="1" y="2" width="14" height="13" rx="2" stroke="currentColor" stroke-width="1.4" fill="none"/>
                <path d="M1 6h14" stroke="currentColor" stroke-width="1.4"/>
                <path d="M5 1v3M11 1v3" stroke="currentColor" stroke-width="1.4" stroke-linecap="round"/>
                <rect x="4" y="8.5" width="2" height="2" rx=".4" fill="currentColor"/>
                <rect x="7" y="8.5" width="2" height="2" rx=".4" fill="currentColor"/>
                <rect x="10" y="8.5" width="2" height="2" rx=".4" fill="currentColor"/>
                <rect x="4" y="11.5" width="2" height="2" rx=".4" fill="currentColor"/>
                <rect x="7" y="11.5" width="2" height="2" rx=".4" fill="currentColor"/>
            </svg>
        </button>
        <div class="cal-popup-overlay" id="calPopupOverlay"></div>
        <div class="cal-popup" id="calPopup" style="display:none;">
            <div class="cal-popup-header">
                <div>
                    <div class="cal-popup-title" id="calPopupTitle">Calendrier</div>
                    <div class="cal-popup-switcher">
                        <button class="active" id="btnMois" onclick="switchCalView('mois')">Mois</button>
                        <button id="btnSemaine" onclick="switchCalView('semaine')">Semaine</button>
                    </div>
                </div>
                <div class="cal-popup-nav">
                    <button onclick="calNavPrev()">&#8592;</button>
                    <button onclick="calNavNext()">&#8594;</button>
                </div>
            </div>
            <div class="cal-popup-body" id="calPopupBody">
                <div style="text-align:center;padding:20px;color:var(--slate-400);font-size:13px;">Chargement...</div>
            </div>
        </div>
    </div>
</div>



<?php if($modeDepartement && $statsGlobalesDept): ?>

<div class="dept-banner">
    <div class="dept-icon-box">D</div>
    <div>
        <div class="dept-title">Departement : <?php echo e($nomDepartement); ?></div>
        <div class="dept-sub">
            <?php echo e($statsGlobalesDept->nb_employes); ?> employe<?php echo e($statsGlobalesDept->nb_employes > 1 ? 's' : ''); ?>

            &middot; <?php echo e(\Carbon\Carbon::create($annee, $mois, 1)->locale('fr')->translatedFormat('F Y')); ?>

            <?php if($dateDebut && $dateFin): ?>
                &middot; <strong><?php echo e(\Carbon\Carbon::parse($dateDebut)->format('d/m')); ?> → <?php echo e(\Carbon\Carbon::parse($dateFin)->format('d/m/Y')); ?></strong>
            <?php endif; ?>
        </div>
    </div>
</div>

<div class="kpi-grid kpi-grid-5">
    <div class="kpi-card cp">
        <div class="kpi-label">Employes</div>
        <div class="kpi-value"><?php echo e($statsGlobalesDept->nb_employes); ?></div>
    </div>
    <div class="kpi-card cb">
        <div class="kpi-label">Heures planifiees</div>
        <div class="kpi-value"><?php echo e(number_format($statsGlobalesDept->heures_planifiees, 1)); ?>h</div>
        <div class="kpi-sub">Pause 1h/j deduite</div>
    </div>
    <div class="kpi-card ct">
        <div class="kpi-label">Heures realisees</div>
        <div class="kpi-value"><?php echo e(number_format($statsGlobalesDept->heures_realisees, 1)); ?>h</div>
        <?php $t = $statsGlobalesDept->taux_realisation; ?>
        <div class="prog-bar"><div class="prog-fill <?php echo e($t >= 90 ? 'prog-g' : ($t >= 70 ? 'prog-a' : 'prog-r')); ?>" style="width:<?php echo e(min($t,100)); ?>%"></div></div>
        <div class="kpi-sub"><?php echo e($t); ?>% du planning</div>
    </div>
    <div class="kpi-card cn">
        <div class="kpi-label">Shift normal</div>
        <div class="kpi-value"><?php echo e(number_format($statsGlobalesDept->heures_shift_normal ?? 0, 1)); ?>h</div>
    </div>
    <div class="kpi-card cgarde">
        <div class="kpi-label">Garde</div>
        <div class="kpi-value"><?php echo e(number_format($statsGlobalesDept->heures_shift_garde ?? 0, 1)); ?>h</div>
    </div>
</div>

<div class="tabs">
    <button class="tab-btn active" onclick="showTab(event,'dept-mensuel')">Vue mensuelle</button>
    <button class="tab-btn" onclick="showTab(event,'dept-semaines')">Par semaine</button>
    <button class="tab-btn" onclick="showTab(event,'dept-employes')">Detail par employe</button>
</div>

<div id="dept-mensuel" class="tab-panel">
    <div class="grid-2-1">
        <div class="card">
            <div class="card-header">Evolution annuelle <?php echo e($annee); ?> — <?php echo e($nomDepartement); ?></div>
            <div class="card-body">
                <div class="chart-legend">
                    <div class="chart-legend-item"><div class="chart-legend-dot" style="background:#e2e8f0;"></div><span>Planifiees</span></div>
                    <div class="chart-legend-item"><div class="chart-legend-dot" style="background:#0d9488;"></div><span>Realisees</span></div>
                    <div class="chart-legend-item"><div class="chart-legend-line" style="background:#d97706;"></div><span>Supp.</span></div>
                </div>
                <div style="position:relative;width:100%;height:260px;">
                    <canvas id="chartDept"></canvas>
                </div>
                <div id="chartDeptCalcs" class="chart-calcs" style="display:none;"></div>
            </div>
        </div>
        <div class="card">
            <div class="card-header">Recapitulatif du mois</div>
            <table class="month-recap-table">
                <tr><td>Heures planifiees</td><td><?php echo e(number_format($statsGlobalesDept->heures_planifiees, 1)); ?>h</td></tr>
                <tr><td>Heures realisees</td><td class="text-teal"><?php echo e(number_format($statsGlobalesDept->heures_realisees, 1)); ?>h</td></tr>
                <tr><td>Heures supplementaires</td><td class="text-amber"><?php echo e(number_format($statsGlobalesDept->heures_supplementaires, 1)); ?>h</td></tr>
                <tr><td>Taux de realisation</td><td><?php echo e($statsGlobalesDept->taux_realisation); ?>%</td></tr>
                <tr class="row-total">
                    <td>Ecart</td>
                    <td class="<?php echo e($statsGlobalesDept->ecart >= 0 ? 'text-green' : 'text-red'); ?>">
                        <?php echo e($statsGlobalesDept->ecart >= 0 ? '+' : ''); ?><?php echo e(number_format($statsGlobalesDept->ecart, 1)); ?>h
                    </td>
                </tr>
                
                <tr class="row-sep"><td colspan="2">Répartition par shift</td></tr>
                <tr>
                    <td>
                        <span class="shift-pill normale"><span class="shift-pill-dot"></span>Shift normal</span>
                    </td>
                    <td style="color:var(--shift-normal-text);font-weight:bold;">
                        <?php echo e(number_format($statsGlobalesDept->heures_shift_normal ?? 0, 1)); ?>h
                    </td>
                </tr>
                <tr>
                    <td>
                        <span class="shift-pill garde"><span class="shift-pill-dot"></span>Garde</span>
                    </td>
                    <td style="color:var(--shift-garde-text);font-weight:bold;">
                        <?php echo e(number_format($statsGlobalesDept->heures_shift_garde ?? 0, 1)); ?>h
                    </td>
                </tr>
            </table>
        </div>
    </div>
</div>

<div id="dept-semaines" class="tab-panel" style="display:none">
    <div class="weeks-grid">
        <?php $__currentLoopData = $semainerDept; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $sem): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <?php $solde = $sem['solde']; $taux = $sem['heures_planifiees'] > 0 ? round(($sem['heures_realisees'] / $sem['heures_planifiees']) * 100) : 0; $headClass = $solde > 0 ? 'wh-positive' : ($solde == 0 ? 'wh-neutral' : 'wh-warning'); ?>
        <div class="week-card">
            <div class="week-head <?php echo e($headClass); ?>">
                <div><div class="wk-title">Semaine <?php echo e($sem['numero']); ?></div><div class="wk-period"><?php echo e($sem['debut']); ?> — <?php echo e($sem['fin']); ?></div></div>
                <div class="wk-solde"><?php echo e($solde >= 0 ? '+' : ''); ?><?php echo e(number_format($solde, 1)); ?>h</div>
            </div>
            <div class="week-stats">
                <div class="wk-stat"><div class="wk-stat-val"><?php echo e(number_format($sem['heures_planifiees'], 1)); ?>h</div><div class="wk-stat-lbl">Planifiees</div></div>
                <div class="wk-stat"><div class="wk-stat-val ct"><?php echo e(number_format($sem['heures_realisees'], 1)); ?>h</div><div class="wk-stat-lbl">Realisees</div></div>
                <div class="wk-stat"><div class="wk-stat-val ca"><?php echo e(number_format($sem['heures_supplementaires'], 1)); ?>h</div><div class="wk-stat-lbl">Supp.</div></div>
            </div>
            <div class="week-footer">
                <div class="week-prog-wrap">
                    <div class="week-prog-track"><div class="week-prog-fill <?php echo e($taux >= 90 ? 'prog-g' : ($taux >= 70 ? 'prog-a' : 'prog-r')); ?>" style="width:<?php echo e(min($taux,100)); ?>%"></div></div>
                    <span style="font-size:12px;font-weight:bold;color:var(--slate-500)"><?php echo e($taux); ?>%</span>
                </div>
            </div>
        </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>
</div>

<div id="dept-employes" class="tab-panel" style="display:none">
    <div class="card">
        <div class="card-header"><span>Detail par employe — <?php echo e($nomDepartement); ?></span></div>
        <div class="emp-table-wrap">
            <table class="emp-table">
                <thead>
                    <tr>
                        <th style="text-align:left">Employe</th>
                        <th>Poste</th>
                        <th>Planifiees</th>
                        <th>Realisees</th>
                        <th>Supp.</th>
                        <th>Shift normal</th>
                        <th>Garde</th>
                        <th>Ecart</th>
                        <th>Taux</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__currentLoopData = $employesDept; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $emp): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php $t = $emp['taux']; ?>
                    <tr>
                        <td><span class="mini-avatar"><?php echo e($emp['initiales']); ?></span><span class="fw-bold"><?php echo e($emp['nom']); ?></span></td>
                        <td style="color:var(--slate-500)"><?php echo e($emp['poste']); ?></td>
                        <td><?php echo e(number_format($emp['planifiees'], 1)); ?>h</td>
                        <td class="text-teal fw-bold"><?php echo e(number_format($emp['realisees'], 1)); ?>h</td>
                        <td class="text-amber"><?php echo e(number_format($emp['supp'], 1)); ?>h</td>
                        <td style="color:var(--shift-normal-text);font-weight:bold;"><?php echo e(number_format($emp['heures_normal'] ?? 0, 1)); ?>h</td>
                        <td style="color:var(--shift-garde-text);font-weight:bold;"><?php echo e(number_format($emp['heures_garde'] ?? 0, 1)); ?>h</td>
                        <td class="<?php echo e($emp['ecart'] >= 0 ? 'text-green' : 'text-red'); ?> fw-bold"><?php echo e($emp['ecart'] >= 0 ? '+' : ''); ?><?php echo e(number_format($emp['ecart'], 1)); ?>h</td>
                        <td><div class="taux-cell"><div class="taux-track"><div class="taux-fill <?php echo e($t >= 90 ? 'prog-g' : ($t >= 70 ? 'prog-a' : 'prog-r')); ?>" style="width:<?php echo e(min($t,100)); ?>%"></div></div><span style="font-size:12px;font-weight:bold;"><?php echo e($t); ?>%</span></div></td>
                        <td><a href="<?php echo e(route('temps.vue-ensemble', ['employee_id' => $emp['id'], 'annee' => $annee, 'mois' => $mois])); ?>" class="link-detail">Voir detail</a></td>
                    </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="2" style="text-align:left">Total departement</td>
                        <td><?php echo e(number_format($statsGlobalesDept->heures_planifiees, 1)); ?>h</td>
                        <td class="text-teal"><?php echo e(number_format($statsGlobalesDept->heures_realisees, 1)); ?>h</td>
                        <td class="text-amber"><?php echo e(number_format($statsGlobalesDept->heures_supplementaires, 1)); ?>h</td>
                        <td style="color:var(--shift-normal-text)"><?php echo e(number_format($statsGlobalesDept->heures_shift_normal ?? 0, 1)); ?>h</td>
                        <td style="color:var(--shift-garde-text)"><?php echo e(number_format($statsGlobalesDept->heures_shift_garde ?? 0, 1)); ?>h</td>
                        <td class="<?php echo e($statsGlobalesDept->ecart >= 0 ? 'text-green' : 'text-red'); ?>"><?php echo e($statsGlobalesDept->ecart >= 0 ? '+' : ''); ?><?php echo e(number_format($statsGlobalesDept->ecart, 1)); ?>h</td>
                        <td colspan="2"><?php echo e($statsGlobalesDept->taux_realisation); ?>%</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>



<?php elseif(!$modeDepartement && $employee && $employee->id): ?>

<div class="emp-banner">
    <div class="emp-avatar">
        <?php echo e(strtoupper(substr($employee->first_name ?? 'U', 0, 1))); ?><?php echo e(strtoupper(substr($employee->last_name ?? '', 0, 1))); ?>

    </div>
    <div class="emp-info">
        <div class="emp-name"><?php echo e($employee->first_name); ?> <?php echo e($employee->last_name); ?></div>
        <div class="emp-sub"><?php echo e($employee->position ?? 'Employe'); ?></div>
        <div class="emp-tags">
            <span class="tag tag-teal"><?php echo e($employee->department ?? 'Service'); ?></span>
            <span class="tag tag-blue"><?php echo e($employee->contract_type ?? 'CDI'); ?></span>
            <span class="tag tag-blue"><?php echo e($employee->work_hours ?? 35); ?>h / semaine</span>
            <?php if($dateDebut && $dateFin): ?>
            <span class="tag tag-amber">
                <?php echo e(\Carbon\Carbon::parse($dateDebut)->format('d/m/Y')); ?> → <?php echo e(\Carbon\Carbon::parse($dateFin)->format('d/m/Y')); ?>

            </span>
            <?php else: ?>
            <span class="tag tag-amber">Pause dejeuner 1h / jour deduite du planning</span>
            <?php endif; ?>
        </div>
    </div>
    <?php if($compteurMois): ?>
    <div class="emp-kpis">
        <div class="emp-kpi">
            <div class="emp-kpi-val"><?php echo e(number_format($compteurMois->heures_planifiees, 0)); ?>h</div>
            <div class="emp-kpi-lbl">Planifiees</div>
        </div>
        <div class="emp-kpi">
            <div class="emp-kpi-val"><?php echo e($compteurMois->jours_travailles); ?></div>
            <div class="emp-kpi-lbl">Jours</div>
        </div>
        <div class="emp-kpi">
            <div class="emp-kpi-val" style="color:<?php echo e($compteurMois->taux_realisation >= 90 ? 'var(--green-600)' : ($compteurMois->taux_realisation >= 70 ? 'var(--amber-600)' : 'var(--red-600)')); ?>">
                <?php echo e($compteurMois->taux_realisation); ?>%
            </div>
            <div class="emp-kpi-lbl">Taux</div>
        </div>
    </div>
    <?php endif; ?>
</div>

<?php if($compteurMois): ?>
<div class="kpi-grid kpi-grid-4" style="grid-template-columns: repeat(6, 1fr);">
    <div class="kpi-card cb">
        <div class="kpi-label">Heures planifiees</div>
        <div class="kpi-value"><?php echo e(number_format($compteurMois->heures_planifiees, 1)); ?>h</div>
        <div class="kpi-sub">Pause 1h/j deduite</div>
    </div>
    <div class="kpi-card ct">
        <div class="kpi-label">Heures realisees</div>
        <div class="kpi-value"><?php echo e(number_format($compteurMois->heures_realisees, 1)); ?>h</div>
        <?php $t = $compteurMois->taux_realisation; ?>
        <div class="prog-bar"><div class="prog-fill <?php echo e($t >= 90 ? 'prog-g' : ($t >= 70 ? 'prog-a' : 'prog-r')); ?>" style="width:<?php echo e(min($t,100)); ?>%"></div></div>
        <div class="kpi-sub"><?php echo e($t); ?>% du planning</div>
    </div>
    <div class="kpi-card ca">
        <div class="kpi-label">Heures supp.</div>
        <div class="kpi-value"><?php echo e(number_format($compteurMois->heures_supplementaires, 1)); ?>h</div>
        <div class="kpi-sub"><?php echo e($compteurMois->jours_travailles); ?> jours travailles</div>
    </div>
    <div class="kpi-card <?php echo e($compteurMois->ecart >= 0 ? 'cg' : 'cr'); ?>">
        <div class="kpi-label">Ecart mensuel</div>
        <div class="kpi-value"><?php echo e($compteurMois->ecart >= 0 ? '+' : ''); ?><?php echo e(number_format($compteurMois->ecart, 1)); ?>h</div>
        <div class="kpi-sub">Realise + supp. vs planifie</div>
    </div>
    
    <div class="kpi-card cn">
        <div class="kpi-label" style="display:flex;align-items:center;gap:5px;">
            <span style="width:8px;height:8px;background:var(--shift-normal-dot);border-radius:50%;display:inline-block;flex-shrink:0;"></span>
            Shift normal
        </div>
        <div class="kpi-value"><?php echo e(number_format($compteurMois->heures_shift_normal ?? 0, 1)); ?>h</div>
        <div class="kpi-sub"><?php echo e($compteurMois->jours_shift_normal ?? 0); ?> jour(s)</div>
    </div>
    
    <div class="kpi-card cgarde">
        <div class="kpi-label" style="display:flex;align-items:center;gap:5px;">
            <span style="width:8px;height:8px;background:var(--shift-garde-dot);border-radius:50%;display:inline-block;flex-shrink:0;"></span>
            Garde
        </div>
        <div class="kpi-value"><?php echo e(number_format($compteurMois->heures_shift_garde ?? 0, 1)); ?>h</div>
        <div class="kpi-sub"><?php echo e($compteurMois->jours_shift_garde ?? 0); ?> jour(s)</div>
    </div>
</div>
<?php endif; ?>

<div class="tabs">
    <button class="tab-btn active" onclick="showTab(event,'emp-mensuel')">Vue mensuelle</button>
    <button class="tab-btn" onclick="showTab(event,'emp-semaines')">Par semaine</button>
    <button class="tab-btn" onclick="showTab(event,'emp-annuel')">Evolution annuelle</button>
</div>



<div id="emp-mensuel" class="tab-panel">

    <div class="grid-2-1" style="margin-bottom:20px;">

        <div class="card">
            <div class="card-header">
                <span>Calendrier — <?php echo e(\Carbon\Carbon::create($annee, $mois, 1)->locale('fr')->translatedFormat('F Y')); ?>

                    <?php if($dateDebut && $dateFin): ?>
                        <span style="font-size:11px;font-weight:normal;color:var(--teal-600);margin-left:6px;">
                             <?php echo e(\Carbon\Carbon::parse($dateDebut)->format('d/m')); ?> → <?php echo e(\Carbon\Carbon::parse($dateFin)->format('d/m')); ?>

                        </span>
                    <?php endif; ?>
                </span>
                <div class="cal-legend">
                    <div class="cal-legend-item"><div class="cal-legend-dot dot-present"></div> Present</div>
                    <div class="cal-legend-item"><div class="cal-legend-dot dot-absent"></div> Absent</div>
                    <div class="cal-legend-item"><div class="cal-legend-dot" style="background:var(--shift-normal-dot)"></div> Shift</div>
                    <div class="cal-legend-item"><div class="cal-legend-dot" style="background:var(--shift-garde-dot)"></div> Garde</div>
                </div>
            </div>
            <div class="card-body">
                <div class="cal-grid-header">
                    <?php $__currentLoopData = ['Lun','Mar','Mer','Jeu','Ven','Sam','Dim']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $n): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="cal-day-name"><?php echo e($n); ?></div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>

                <?php
                    $premierJour = \Carbon\Carbon::create($annee, $mois, 1);
                    $decalage    = $premierJour->dayOfWeek === 0 ? 6 : $premierJour->dayOfWeek - 1;
                    // Index jours par date pour accès rapide
                    $joursIdx = collect($joursDetails)->keyBy('date');
                ?>
                <div class="cal-grid">
                    <?php for($i = 0; $i < $decalage; $i++): ?>
                        <div class="cal-cell cal-empty"></div>
                    <?php endfor; ?>

                    <?php $__currentLoopData = $joursDetails; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $jour): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php
                        $st = $jour['statut'];
                        $shiftType = $jour['shift_type'] ?? 'normal';
                        $isGarde   = $shiftType === 'garde';

                        $dotClass = match($st) {
                            'present'  => 'dot-present',
                            'absent'   => 'dot-absent',
                            'planifie' => 'dot-planifie',
                            default    => 'dot-none',
                        };
                        $hrClass = match($st) {
                            'present'  => 'color-present',
                            'absent'   => 'color-absent',
                            'planifie' => 'color-planifie',
                            default    => 'color-non-plan',
                        };

                        // Un jour est traité pareil qu'il tombe un weekend ou non : certains
                        // employes travaillent le samedi/dimanche (garde, astreinte...).
                        $cellClass = 'cal-cell';
                        if ($jour['is_today'])       $cellClass .= ' cal-today';
                        if ($st === 'present') {
                            $cellClass .= $isGarde ? ' cal-garde' : ' cal-normal-shift';
                        }
                    ?>
                    <div class="<?php echo e($cellClass); ?>"
                         title="<?php echo e(ucfirst($jour['nom_jour_complet'])); ?> <?php echo e($jour['jour']); ?> - <?php echo e($st); ?><?php echo e($isGarde ? ' (Garde)' : ''); ?>">
                        <div class="cal-short-day"><?php echo e($jour['nom_jour']); ?></div>
                        <div class="cal-num"><?php echo e($jour['jour']); ?></div>

                        <?php if($jour['heures_realisees'] > 0): ?>
                            <div class="cal-hours <?php echo e($hrClass); ?>"><?php echo e(number_format($jour['heures_realisees'], 1)); ?>h</div>
                            
                            <div class="cal-shift-badge <?php echo e($isGarde ? 'garde' : 'normal'); ?>">
                                <?php echo e($isGarde ? '🌙 Garde' : '☀ Shift'); ?>

                            </div>
                        <?php elseif($jour['heures_planifiees'] > 0): ?>
                            <div class="cal-hours color-planifie">— / <?php echo e(number_format($jour['heures_planifiees'], 1)); ?>h</div>
                        <?php endif; ?>

                        <?php if($jour['shift_start'] && $jour['shift_end']): ?>
                            <div class="cal-shift"><?php echo e(substr($jour['shift_start'],0,5)); ?>-<?php echo e(substr($jour['shift_end'],0,5)); ?></div>
                        <?php endif; ?>

                        <?php if($jour['heures_realisees'] > 0 && $jour['heures_planifiees'] > 0): ?>
                            <div class="cal-ecart <?php echo e($jour['ecart'] >= 0 ? 'color-pos' : 'color-neg'); ?>">
                                <?php echo e($jour['ecart'] >= 0 ? '+' : ''); ?><?php echo e(number_format($jour['ecart'], 1)); ?>h
                            </div>
                        <?php endif; ?>

                        <span class="cal-dot <?php echo e($dotClass); ?>"></span>
                    </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </div>
        </div>

        <div style="display:flex;flex-direction:column;gap:14px;">
            <div class="card">
                <div class="card-header">Recapitulatif du mois</div>
                <?php if($compteurMois): ?>
                <table class="month-recap-table">
                    <tr><td>Heures planifiees</td><td><?php echo e(number_format($compteurMois->heures_planifiees, 1)); ?>h</td></tr>
                    <tr><td>Heures realisees</td><td class="text-teal"><?php echo e(number_format($compteurMois->heures_realisees, 1)); ?>h</td></tr>
                    <tr><td>Heures supplementaires</td><td class="text-amber"><?php echo e(number_format($compteurMois->heures_supplementaires, 1)); ?>h</td></tr>
                    <tr><td>Jours travailles</td><td><?php echo e($compteurMois->jours_travailles); ?> j</td></tr>
                    <tr><td>Taux de realisation</td><td><?php echo e($compteurMois->taux_realisation); ?>%</td></tr>
                    <tr class="row-total">
                        <td>Ecart</td>
                        <td class="<?php echo e($compteurMois->ecart >= 0 ? 'text-green' : 'text-red'); ?>">
                            <?php echo e($compteurMois->ecart >= 0 ? '+' : ''); ?><?php echo e(number_format($compteurMois->ecart, 1)); ?>h
                        </td>
                    </tr>

                    
                    <tr class="row-sep"><td colspan="2">Heures par type de shift</td></tr>
                    <tr>
                        <td>
                            <span class="shift-pill normale">
                                <span class="shift-pill-dot"></span>Shift normal
                            </span>
                        </td>
                        <td style="color:var(--shift-normal-text);font-weight:bold;">
                            <?php echo e(number_format($compteurMois->heures_shift_normal ?? 0, 1)); ?>h
                            <span style="color:var(--slate-400);font-weight:normal;font-size:11px;">
                                (<?php echo e($compteurMois->jours_shift_normal ?? 0); ?> j)
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <span class="shift-pill garde">
                                <span class="shift-pill-dot"></span>Garde
                            </span>
                        </td>
                        <td style="color:var(--shift-garde-text);font-weight:bold;">
                            <?php echo e(number_format($compteurMois->heures_shift_garde ?? 0, 1)); ?>h
                            <span style="color:var(--slate-400);font-weight:normal;font-size:11px;">
                                (<?php echo e($compteurMois->jours_shift_garde ?? 0); ?> j)
                            </span>
                        </td>
                    </tr>
                    <?php if(($compteurMois->heures_shift_normal ?? 0) + ($compteurMois->heures_shift_garde ?? 0) > 0): ?>
                    <tr>
                        <td style="font-size:11px;color:var(--slate-400);">Plan. shift normal</td>
                        <td style="font-size:11px;color:var(--slate-500);"><?php echo e(number_format($compteurMois->plan_shift_normal ?? 0, 1)); ?>h</td>
                    </tr>
                    <tr>
                        <td style="font-size:11px;color:var(--slate-400);">Plan. garde</td>
                        <td style="font-size:11px;color:var(--slate-500);"><?php echo e(number_format($compteurMois->plan_shift_garde ?? 0, 1)); ?>h</td>
                    </tr>
                    <?php endif; ?>
                </table>

                
                <?php if(($compteurMois->heures_shift_normal ?? 0) + ($compteurMois->heures_shift_garde ?? 0) > 0): ?>
                <?php
                    $totalShift = ($compteurMois->heures_shift_normal ?? 0) + ($compteurMois->heures_shift_garde ?? 0);
                    $pctNormal  = $totalShift > 0 ? round(($compteurMois->heures_shift_normal / $totalShift) * 100) : 0;
                    $pctGarde   = 100 - $pctNormal;
                ?>
                <div style="padding:12px 14px;border-top:1px solid var(--slate-100);">
                    <div style="font-size:11px;color:var(--slate-400);margin-bottom:6px;font-weight:bold;text-transform:uppercase;letter-spacing:.05em;">Répartition</div>
                    <div style="display:flex;height:10px;border-radius:99px;overflow:hidden;gap:2px;">
                        <?php if($pctNormal > 0): ?>
                        <div style="flex:<?php echo e($pctNormal); ?>;background:var(--shift-normal-dot);border-radius:99px 0 0 99px;" title="Shift normal <?php echo e($pctNormal); ?>%"></div>
                        <?php endif; ?>
                        <?php if($pctGarde > 0): ?>
                        <div style="flex:<?php echo e($pctGarde); ?>;background:var(--shift-garde-dot);border-radius:0 99px 99px 0;" title="Garde <?php echo e($pctGarde); ?>%"></div>
                        <?php endif; ?>
                    </div>
                    <div style="display:flex;justify-content:space-between;margin-top:5px;font-size:10px;font-weight:bold;">
                        <span style="color:var(--shift-normal-text)">Shift <?php echo e($pctNormal); ?>%</span>
                        <span style="color:var(--shift-garde-text)">Garde <?php echo e($pctGarde); ?>%</span>
                    </div>
                </div>
                <?php endif; ?>

                <?php endif; ?>
            </div>

            <div class="card">
                <div class="card-header">Regles de calcul</div>
                <div class="card-body" style="font-size:12px;line-height:1.8;color:var(--slate-500)">
                    <div><strong>Planifiees</strong> : duree shift (planning) &minus; 1h pause dejeuner.</div>
                    <div><strong>Realisees</strong> : heures enregistrees dans le pointage (nettes).</div>
                    <div><strong>Ecart</strong> : (realisees + supp.) &minus; planifiees.</div>
                    <div><strong style="color:var(--shift-normal-text)">Shift normal</strong> : heures des journees 7h–19h.</div>
                    <div><strong style="color:var(--shift-garde-text)">Garde</strong> : heures des shifts 19h–7h (24h).</div>
                </div>
            </div>
        </div>
    </div>

    
    <div class="card">
        <div class="card-header">
            <span>Detail journalier</span>
            <span style="font-size:12px;font-weight:normal;color:var(--slate-500)"><?php echo e(count($joursDetails)); ?> jours</span>
        </div>
        <div class="day-table-wrap">
            <table class="day-table">
                <thead>
                    <tr>
                        <th style="text-align:left">Jour</th>
                        <th>Shift type</th>
                        <th>Horaire planifie</th>
                        <th>Planifiees</th>
                        <th>Realisees</th>
                        <th>Supp.</th>
                        <th>Ecart</th>
                        <th>Entree</th>
                        <th>Sortie</th>
                        <th>Statut</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__currentLoopData = $joursDetails; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $jour): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php
                        $st        = $jour['statut'];
                        $shiftType = $jour['shift_type'] ?? 'normal';
                        $isGarde   = $shiftType === 'garde';
                        // Un jour est traité pareil qu'il tombe un weekend ou non : certains
                        // employes travaillent le samedi/dimanche (garde, astreinte...).
                        $rowClass  = $jour['is_today'] ? 'row-today' : ($isGarde ? 'row-garde' : 'row-normal');
                        $badgeClass = match($st) { 'present' => 'sb-present', 'absent' => 'sb-absent', 'planifie' => 'sb-planifie', default => 'sb-none' };
                        $badgeLabel = match($st) { 'present' => 'Present', 'absent' => 'Absent', 'planifie' => 'Planifie', default => '—' };
                    ?>
                    <tr class="<?php echo e($rowClass); ?>">
                        <td style="text-align:left">
                            <span style="font-weight:bold"><?php echo e(ucfirst($jour['nom_jour'])); ?></span>
                            <span style="color:var(--slate-400);margin-left:4px"><?php echo e($jour['jour']); ?></span>
                        </td>
                        <td>
                            <span class="shift-pill <?php echo e($isGarde ? 'garde' : 'normale'); ?>">
                                <span class="shift-pill-dot"></span>
                                <?php echo e($isGarde ? 'Garde' : 'Shift'); ?>

                            </span>
                        </td>
                        <td style="font-size:12px;color:var(--slate-500)">
                            <?php if($jour['shift_start'] && $jour['shift_end']): ?>
                                <?php echo e(substr($jour['shift_start'],0,5)); ?> &rarr; <?php echo e(substr($jour['shift_end'],0,5)); ?>

                            <?php else: ?>
                                <span style="color:var(--slate-300)">—</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if($jour['heures_planifiees'] > 0): ?> <?php echo e(number_format($jour['heures_planifiees'], 1)); ?>h
                            <?php else: ?> <span style="color:var(--slate-300)">—</span>
                            <?php endif; ?>
                        </td>
                        <td class="<?php echo e($jour['heures_realisees'] > 0 ? 'text-teal fw-bold' : ''); ?>">
                            <?php if($jour['heures_realisees'] > 0): ?> <?php echo e(number_format($jour['heures_realisees'], 1)); ?>h
                            <?php else: ?> <span style="color:var(--slate-300)">—</span>
                            <?php endif; ?>
                        </td>
                        <td class="<?php echo e($jour['heures_supplementaires'] > 0 ? 'text-amber' : ''); ?>">
                            <?php if($jour['heures_supplementaires'] > 0): ?> <?php echo e(number_format($jour['heures_supplementaires'], 1)); ?>h
                            <?php else: ?> <span style="color:var(--slate-300)">—</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if($jour['heures_planifiees'] > 0 && $jour['heures_realisees'] > 0): ?>
                                <span class="<?php echo e($jour['ecart'] >= 0 ? 'text-green' : 'text-red'); ?> fw-bold">
                                    <?php echo e($jour['ecart'] >= 0 ? '+' : ''); ?><?php echo e(number_format($jour['ecart'], 1)); ?>h
                                </span>
                            <?php else: ?> <span style="color:var(--slate-300)">—</span>
                            <?php endif; ?>
                        </td>
                        <td style="font-size:12px;color:var(--slate-500)"><?php echo e($jour['heure_entree'] ? substr($jour['heure_entree'],0,5) : '—'); ?></td>
                        <td style="font-size:12px;color:var(--slate-500)"><?php echo e($jour['heure_sortie'] ? substr($jour['heure_sortie'],0,5) : '—'); ?></td>
                        <td><span class="status-badge <?php echo e($badgeClass); ?>"><?php echo e($badgeLabel); ?></span></td>
                    </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </tbody>
                <?php if($compteurMois): ?>
                <tfoot>
                    <tr>
                        <td colspan="3" style="text-align:left">Total du mois</td>
                        <td><?php echo e(number_format($compteurMois->heures_planifiees, 1)); ?>h</td>
                        <td class="text-teal"><?php echo e(number_format($compteurMois->heures_realisees, 1)); ?>h</td>
                        <td class="text-amber"><?php echo e(number_format($compteurMois->heures_supplementaires, 1)); ?>h</td>
                        <td class="<?php echo e($compteurMois->ecart >= 0 ? 'text-green' : 'text-red'); ?>">
                            <?php echo e($compteurMois->ecart >= 0 ? '+' : ''); ?><?php echo e(number_format($compteurMois->ecart, 1)); ?>h
                        </td>
                        <td colspan="3" style="color:var(--slate-400);text-align:right"><?php echo e($compteurMois->jours_travailles); ?> j travailles</td>
                    </tr>
                </tfoot>
                <?php endif; ?>
            </table>
        </div>
    </div>
</div>



<div id="emp-semaines" class="tab-panel" style="display:none">
    <div class="weeks-grid">
        <?php $__currentLoopData = $semaines; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $sem): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <?php $solde = $sem['solde']; $taux = $sem['taux']; $headClass = $solde > 0 ? 'wh-positive' : ($solde == 0 ? 'wh-neutral' : 'wh-warning'); ?>
        <div class="week-card">
            <div class="week-head <?php echo e($headClass); ?>">
                <div><div class="wk-title">Semaine <?php echo e($sem['numero']); ?></div><div class="wk-period"><?php echo e($sem['debut']); ?> — <?php echo e($sem['fin']); ?></div></div>
                <div class="wk-solde"><?php echo e($solde >= 0 ? '+' : ''); ?><?php echo e(number_format($solde, 1)); ?>h</div>
            </div>
            <div class="week-stats">
                <div class="wk-stat"><div class="wk-stat-val"><?php echo e(number_format($sem['heures_planifiees'], 1)); ?>h</div><div class="wk-stat-lbl">Planifiees</div></div>
                <div class="wk-stat"><div class="wk-stat-val ct"><?php echo e(number_format($sem['heures_realisees'], 1)); ?>h</div><div class="wk-stat-lbl">Realisees</div></div>
                <div class="wk-stat"><div class="wk-stat-val ca"><?php echo e(number_format($sem['heures_supplementaires'], 1)); ?>h</div><div class="wk-stat-lbl">Supp.</div></div>
            </div>
            <div class="week-footer">
                <span style="font-size:12px;color:var(--slate-500)"><?php echo e($sem['jours_travailles']); ?> j travailles</span>
                <div class="week-prog-wrap" style="max-width:100px">
                    <div class="week-prog-track"><div class="week-prog-fill <?php echo e($taux >= 90 ? 'prog-g' : ($taux >= 70 ? 'prog-a' : 'prog-r')); ?>" style="width:<?php echo e(min($taux,100)); ?>%"></div></div>
                    <span style="font-size:12px;font-weight:bold;color:var(--slate-500)"><?php echo e($taux); ?>%</span>
                </div>
            </div>
        </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>

    <?php if(count($semaines) > 0): ?>
    <?php $totPlan = collect($semaines)->sum('heures_planifiees'); $totReal = collect($semaines)->sum('heures_realisees'); $totSupp = collect($semaines)->sum('heures_supplementaires'); $totTotal = collect($semaines)->sum('total'); $totSolde = collect($semaines)->sum('solde'); $totJours = collect($semaines)->sum('jours_travailles'); ?>
    <div class="card" style="margin-top:16px">
        <div class="card-header">Tableau recapitulatif — semaines</div>
        <div class="recap-table-wrap">
            <table class="recap-table">
                <thead><tr><th style="text-align:left">Semaine</th><th>Periode</th><th>Planifiees</th><th>Realisees</th><th>Supp.</th><th>Total</th><th>Solde</th><th>Jours</th></tr></thead>
                <tbody>
                    <?php $__currentLoopData = $semaines; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $sem): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <tr>
                        <td>Sem. <?php echo e($sem['numero']); ?></td>
                        <td style="color:var(--slate-400);font-size:12px"><?php echo e($sem['debut']); ?> — <?php echo e($sem['fin']); ?></td>
                        <td><?php echo e(number_format($sem['heures_planifiees'], 1)); ?>h</td>
                        <td class="text-teal"><?php echo e(number_format($sem['heures_realisees'], 1)); ?>h</td>
                        <td class="text-amber"><?php echo e(number_format($sem['heures_supplementaires'], 1)); ?>h</td>
                        <td class="fw-bold"><?php echo e(number_format($sem['total'], 1)); ?>h</td>
                        <td class="<?php echo e($sem['solde'] >= 0 ? 'text-green' : 'text-red'); ?> fw-bold"><?php echo e($sem['solde'] >= 0 ? '+' : ''); ?><?php echo e(number_format($sem['solde'], 1)); ?>h</td>
                        <td style="color:var(--slate-500)"><?php echo e($sem['jours_travailles']); ?> j</td>
                    </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="2" style="text-align:left">Total</td>
                        <td><?php echo e(number_format($totPlan, 1)); ?>h</td>
                        <td class="text-teal"><?php echo e(number_format($totReal, 1)); ?>h</td>
                        <td class="text-amber"><?php echo e(number_format($totSupp, 1)); ?>h</td>
                        <td><?php echo e(number_format($totTotal, 1)); ?>h</td>
                        <td class="<?php echo e($totSolde >= 0 ? 'text-green' : 'text-red'); ?>"><?php echo e($totSolde >= 0 ? '+' : ''); ?><?php echo e(number_format($totSolde, 1)); ?>h</td>
                        <td><?php echo e($totJours); ?> j</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
    <?php endif; ?>
</div>


<div id="emp-annuel" class="tab-panel" style="display:none">
    <div class="card">
        <div class="card-header">Evolution annuelle <?php echo e($annee); ?> — <?php echo e($employee->first_name); ?> <?php echo e($employee->last_name); ?></div>
        <div class="card-body">
            <div class="chart-legend">
                <div class="chart-legend-item"><div class="chart-legend-dot" style="background:#e2e8f0;"></div><span>Planifiees</span></div>
                <div class="chart-legend-item"><div class="chart-legend-dot" style="background:#0d9488;"></div><span>Realisees</span></div>
                <div class="chart-legend-item"><div class="chart-legend-line" style="background:#d97706;"></div><span>Supp.</span></div>
            </div>
            <div style="position:relative;width:100%;height:260px;">
                <canvas id="chartAnnuel"></canvas>
            </div>
            <div id="chartAnnuelCalcs" class="chart-calcs" style="display:none;"></div>
        </div>
    </div>
</div>



<?php else: ?>
<div class="empty-state">
    <div class="empty-title">Selectionnez un employe ou un departement</div>
    <div class="empty-sub">Utilisez les filtres ci-dessus pour afficher les donnees de temps de travail.</div>
</div>
<?php endif; ?>

</div>
<?php $__env->stopSection(); ?>


<?php $__env->startPush('scripts'); ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
const GARDE_SHIFTS = <?php echo json_encode($workingData['garde_shifts'] ?? [], 15, 512) ?>;

// =========================================================================
// ONGLETS
// =========================================================================
function showTab(e, id) {
    var tabs = e.target.closest('.tabs');
    if (tabs) tabs.querySelectorAll('.tab-btn').forEach(function(b) { b.classList.remove('active'); });
    e.target.classList.add('active');
    var panel = document.getElementById(id);
    if (!panel) return;
    panel.parentElement.querySelectorAll('.tab-panel').forEach(function(p) { p.style.display = 'none'; });
    panel.style.display = '';
}

// =========================================================================
// GRAPHIQUES
// =========================================================================
function buildChart(canvasId, data, calcsId) {
    var ctx = document.getElementById(canvasId);
    if (!ctx || !data || data.length === 0) return;

    var TEAL = '#0d9488', GREY = '#e2e8f0', AMBER = '#d97706';
    var labels = data.map(function(d) { return d.mois; });
    var plan   = data.map(function(d) { return parseFloat(d.heures_planifiees) || 0; });
    var real   = data.map(function(d) { return parseFloat(d.heures_realisees)  || 0; });
    var supp   = data.map(function(d) { return parseFloat(d.heures_supp)       || 0; });

    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [
                { label: 'Planifiees', data: plan, backgroundColor: GREY, borderRadius: 3, borderSkipped: false, order: 2 },
                { label: 'Realisees', data: real, backgroundColor: TEAL, borderRadius: 3, borderSkipped: false, order: 1 },
                { type: 'line', label: 'Supp.', data: supp, borderColor: AMBER, backgroundColor: 'transparent', borderWidth: 2, pointRadius: 4, pointBackgroundColor: AMBER, pointBorderColor: '#fff', pointBorderWidth: 1.5, tension: 0.3, order: 0 }
            ]
        },
        options: {
            responsive: true, maintainAspectRatio: false,
            interaction: { mode: 'index', intersect: false },
            plugins: {
                legend: { display: false },
                tooltip: { backgroundColor: '#1e293b', titleColor: '#f1f5f9', bodyColor: '#cbd5e1', padding: 10, cornerRadius: 6, callbacks: { label: function(ctx) { return ' ' + ctx.dataset.label + ' : ' + ctx.parsed.y.toFixed(1) + 'h'; } } }
            },
            scales: {
                x: { grid: { display: false }, ticks: { font: { family: 'Arial, Helvetica, sans-serif', size: 11 }, color: '#64748b', autoSkip: false } },
                y: { beginAtZero: true, grid: { color: '#f1f5f9' }, ticks: { font: { family: 'Arial, Helvetica, sans-serif', size: 11 }, color: '#64748b', callback: function(v) { return v + 'h'; } } }
            }
        }
    });

    var moisActifs  = data.filter(function(d) { return (parseFloat(d.heures_planifiees) || 0) > 0 || (parseFloat(d.heures_realisees) || 0) > 0; });
    var totalPlan   = plan.reduce(function(a,b){return a+b;},0);
    var totalReal   = real.reduce(function(a,b){return a+b;},0);
    var totalSupp   = supp.reduce(function(a,b){return a+b;},0);
    var ecart       = totalReal - totalPlan;
    var taux        = totalPlan > 0 ? Math.round((totalReal / totalPlan) * 100) : 0;
    var maxReal     = Math.max.apply(null, real.filter(function(v){return v>0;}));
    var meilIdx     = real.indexOf(maxReal);
    var meilLabel   = meilIdx >= 0 ? labels[meilIdx] : '—';
    var realActifs  = real.filter(function(v){return v>0;});
    var minReal     = realActifs.length > 0 ? Math.min.apply(null, realActifs) : 0;
    var pireIdx     = real.indexOf(minReal);
    var pireLabel   = pireIdx >= 0 && minReal > 0 ? labels[pireIdx] : '—';
    var moyReal     = moisActifs.length > 0 ? (totalReal / moisActifs.length) : 0;
    var ecartColor  = ecart >= 0 ? '#16a34a' : '#dc2626';
    var tauxColor   = taux >= 90 ? '#16a34a' : (taux >= 70 ? '#d97706' : '#dc2626');

    var box = document.getElementById(calcsId);
    if (!box) return;
    box.style.display = '';
    box.innerHTML =
        '<span style="font-weight:bold;color:#334155;font-size:13px;">Calculs annuels</span><span style="color:#cbd5e1;margin:0 8px;">|</span>' +
        '<strong>Mois avec donnees :</strong> ' + moisActifs.length + ' / ' + data.length +
        '<br><strong>Planifiees totales :</strong> ' + totalPlan.toFixed(1) + 'h&nbsp;&nbsp;' +
        '<strong style="color:' + TEAL + '">Realisees totales :</strong> ' + totalReal.toFixed(1) + 'h&nbsp;&nbsp;' +
        '<strong style="color:' + AMBER + '">Supp. totales :</strong> ' + totalSupp.toFixed(1) + 'h' +
        '<br><strong>Ecart annuel :</strong> <span style="color:' + ecartColor + ';font-weight:bold;">' + (ecart >= 0 ? '+' : '') + ecart.toFixed(1) + 'h</span>&nbsp;&nbsp;' +
        '<strong>Taux :</strong> <span style="color:' + tauxColor + ';font-weight:bold;">' + taux + '%</span>&nbsp;&nbsp;' +
        '<strong>Moyenne/mois :</strong> ' + moyReal.toFixed(1) + 'h' +
        '<br><strong>Meilleur mois :</strong> ' + meilLabel + ' (' + maxReal.toFixed(1) + 'h)' +
        (pireLabel !== meilLabel && pireLabel !== '—' ? '&nbsp;&nbsp;<strong>Mois le plus faible :</strong> ' + pireLabel + ' (' + minReal.toFixed(1) + 'h)' : '');
}

// =========================================================================
// CALENDRIER POPUP
// =========================================================================
(function () {
    var PLANNING_JOURS = <?php echo json_encode($joursPlanningSemaine ?? [], 15, 512) ?>;
    var calView = 'mois', calAnnee = <?php echo e($annee); ?>, calMois = <?php echo e($mois); ?>, calWeekOffset = 0;
    var today = new Date();
    var JOURS_COURTS = ['Lun','Mar','Mer','Jeu','Ven','Sam','Dim'];
    var MOIS_FR = ['Janvier','Fevrier','Mars','Avril','Mai','Juin','Juillet','Aout','Septembre','Octobre','Novembre','Decembre'];

    var btn = document.getElementById('calPopupBtn'), popup = document.getElementById('calPopup'), overlay = document.getElementById('calPopupOverlay');
    if (!btn || !popup || !overlay) return;

    btn.addEventListener('click', function(e) {
        e.stopPropagation();
        var isOpen = popup.style.display !== 'none';
        if (isOpen) { closeCalPopup(); } else { popup.style.display = ''; overlay.classList.add('open'); calWeekOffset = 0; renderCal(); }
    });
    overlay.addEventListener('click', closeCalPopup);
    function closeCalPopup() { popup.style.display = 'none'; overlay.classList.remove('open'); }

    window.switchCalView = function(v) {
        calView = v;
        document.getElementById('btnMois').classList.toggle('active', v === 'mois');
        document.getElementById('btnSemaine').classList.toggle('active', v === 'semaine');
        calWeekOffset = 0; renderCal();
    };
    window.calNavPrev = function() { if (calView === 'mois') { calMois--; if (calMois < 1) { calMois = 12; calAnnee--; } } else { calWeekOffset--; } renderCal(); };
    window.calNavNext = function() { if (calView === 'mois') { calMois++; if (calMois > 12) { calMois = 1; calAnnee++; } } else { calWeekOffset++; } renderCal(); };

    function pad(n) { return String(n).padStart(2,'0'); }
    function toDateStr(y,m,d) { return y+'-'+pad(m)+'-'+pad(d); }
    function isPlanifie(ds) { return PLANNING_JOURS && PLANNING_JOURS[ds] !== undefined; }
    function getShift(ds) { if (!PLANNING_JOURS || !PLANNING_JOURS[ds]) return null; var s = PLANNING_JOURS[ds]; if (!s.shift_start || !s.shift_end) return null; return String(s.shift_start).substr(0,5)+'\u2013'+String(s.shift_end).substr(0,5); }
    function isToday(y,m,d) { return today.getFullYear()===y && (today.getMonth()+1)===m && today.getDate()===d; }
    function isWeekend(dow) { return dow===0||dow===6; }

    function renderCal() {
        var title = document.getElementById('calPopupTitle'), body = document.getElementById('calPopupBody');
        if (calView === 'mois') { title.textContent = MOIS_FR[calMois-1]+' '+calAnnee; body.innerHTML = renderMois(); }
        else { var sem = getSemaineCourante(); title.textContent = 'Sem. '+sem.label; body.innerHTML = renderSemaine(sem); }
    }

    function renderMois() {
        var firstDay = new Date(calAnnee, calMois-1, 1), lastDay = new Date(calAnnee, calMois, 0);
        var startDow = (firstDay.getDay()+6)%7, totalDays = lastDay.getDate();
        var html = '<div class="cal-popup-daynames">';
        JOURS_COURTS.forEach(function(j){html+='<div class="cal-popup-dayname">'+j+'</div>';});
        html+='</div><div class="cal-popup-grid">';
        for (var i=0;i<startDow;i++) html+='<div class="cal-popup-cell"></div>';
        for (var d=1;d<=totalDays;d++) {
            var ds=toDateStr(calAnnee,calMois,d), date=new Date(calAnnee,calMois-1,d), dow=date.getDay();
            var wkd=isWeekend(dow), plan=isPlanifie(ds)&&!wkd, tod=isToday(calAnnee,calMois,d);
            var cls='cal-popup-cell';
            if(tod)cls+=' cp-today'; else if(wkd)cls+=' cp-weekend'; else if(plan)cls+=' cp-planifie';
            html+='<div class="'+cls+'">'+d+'</div>';
        }
        var total=startDow+totalDays, reste=total%7!==0?7-(total%7):0;
        for(var j=0;j<reste;j++) html+='<div class="cal-popup-cell cp-other-month"></div>';
        html+='</div><div class="cal-popup-legend">';
        html+='<div class="cal-popup-legend-item"><div class="cal-popup-legend-dot" style="background:var(--teal-500);"></div><span>Planifie</span></div>';
        html+='<div class="cal-popup-legend-item"><div class="cal-popup-legend-dot" style="background:var(--teal-600);"></div><span>Aujourd\'hui</span></div>';
        html+='</div>';
        return html;
    }

    function getSemaineCourante() {
        var base=new Date(calAnnee,calMois-1,1), dow=(base.getDay()+6)%7;
        base.setDate(base.getDate()-dow+(calWeekOffset*7));
        var days=[]; for(var i=0;i<7;i++){var d=new Date(base);d.setDate(base.getDate()+i);days.push(d);}
        var debut=days[0],fin=days[6];
        var label=pad(debut.getDate())+'/'+pad(debut.getMonth()+1)+' \u2013 '+pad(fin.getDate())+'/'+pad(fin.getMonth()+1);
        return {days:days,label:label};
    }

    function renderSemaine(sem) {
        var days=sem.days, html='<div class="cal-week-label">Semaine du '+sem.label+'</div><div class="cal-week-grid">';
        days.forEach(function(d){
            var y=d.getFullYear(),m=d.getMonth()+1,day=d.getDate(),ds=toDateStr(y,m,day);
            var dow=d.getDay(),wkd=isWeekend(dow),tod=isToday(y,m,day),plan=isPlanifie(ds)&&!wkd,shift=getShift(ds);
            var nomJ=JOURS_COURTS[(dow+6)%7];
            var cls='cal-week-cell'; if(tod)cls+=' cw-today'; else if(wkd)cls+=' cw-weekend'; else if(plan)cls+=' cw-planifie';
            html+='<div class="'+cls+'"><div class="cal-week-dayname">'+nomJ+'</div><div class="cal-week-num">'+day+'</div>';
            if(shift&&plan)html+='<div class="cal-week-shift">'+shift+'</div>';
            html+='</div>';
        });
        html+='</div><div class="cal-popup-legend" style="margin-top:10px;padding-top:8px;border-top:1px solid var(--slate-100);">';
        html+='<div class="cal-popup-legend-item"><div class="cal-popup-legend-dot" style="background:var(--teal-500);"></div><span>Planifie</span></div>';
        html+='</div>';
        return html;
    }
})();

// =========================================================================
// INIT GRAPHIQUES
// =========================================================================
document.addEventListener('DOMContentLoaded', function() {
    buildChart('chartAnnuel', <?php echo json_encode($graphiqueMois ?? [], 15, 512) ?>,     'chartAnnuelCalcs');
    buildChart('chartDept',   <?php echo json_encode($graphiqueMoisDept ?? [], 15, 512) ?>, 'chartDeptCalcs');
});
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\Projects\SIRH-\resources\views/vue-ensemble/index.blade.php ENDPATH**/ ?>