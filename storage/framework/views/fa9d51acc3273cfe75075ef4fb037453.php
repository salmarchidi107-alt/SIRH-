<?php $__env->startSection('title', 'Vue d\'ensemble'); ?>
<?php $__env->startSection('page-title', 'Vue d\'ensemble du temps de travail'); ?>

<?php $__env->startPush('styles'); ?>
<style>
/* ── Base ── */
.employee-card { background:white; border-radius:12px; padding:20px; display:flex; align-items:center; gap:20px; margin-bottom:20px; box-shadow:0 1px 3px rgba(0,0,0,.08); }
.employee-avatar { width:60px; height:60px; border-radius:50%; background:linear-gradient(135deg,#0f6b7c,#00c9a7); display:flex; align-items:center; justify-content:center; font-weight:700; color:white; font-size:1.2rem; flex-shrink:0; }
.employee-info h3 { font-size:1.1rem; margin-bottom:2px; }
.employee-info p  { color:#64748b; font-size:.85rem; }
.employee-meta { margin-left:auto; display:flex; gap:20px; }
.meta-value { font-weight:700; color:#0f6b7c; font-size:1rem; }
.meta-label { font-size:.7rem; color:#64748b; }

.stats-grid { display:grid; grid-template-columns:repeat(4,1fr); gap:15px; margin-bottom:20px; }
.stat-card { background:white; padding:16px; border-radius:10px; text-align:center; box-shadow:0 1px 3px rgba(0,0,0,.08); }
.stat-card.primary { border-top:3px solid #0f6b7c; }
.stat-card.info    { border-top:3px solid #3b82f6; }
.stat-card.warning { border-top:3px solid #f59e0b; }
.stat-card.success { border-top:3px solid #10b981; }
.stat-card.purple  { border-top:3px solid #8b5cf6; }
.stat-value { font-size:1.5rem; font-weight:800; }
.stat-label { font-size:.75rem; color:#64748b; }

.content-grid { display:grid; grid-template-columns:2fr 1fr; gap:20px; }
.card { background:white; border-radius:10px; box-shadow:0 1px 3px rgba(0,0,0,.08); }
.card-header { padding:14px; border-bottom:1px solid #e2e8f0; font-weight:700; display:flex; align-items:center; justify-content:space-between; }
.card-body { padding:16px; }
.chart-container { height:240px; }

.data-table { width:100%; border-collapse:collapse; }
.data-table th { background:#f8fafc; padding:10px; font-size:.65rem; text-transform:uppercase; }
.data-table td { padding:10px; border-bottom:1px solid #f1f5f9; text-align:center; }
.data-table td:first-child { text-align:left; font-weight:600; }
.total-row { background:#e6f4f7; font-weight:700; }
.positive { color:#10b981; font-weight:600; }
.negative { color:#ef4444; font-weight:600; }

.weeks-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(250px,1fr)); gap:15px; margin-top:20px; }
.week-card { background:white; border-radius:10px; overflow:hidden; box-shadow:0 1px 3px rgba(0,0,0,.08); }
.week-header { background:#0f6b7c; color:white; padding:10px; display:flex; justify-content:space-between; font-weight:600; font-size:.85rem; }
.week-table { width:100%; font-size:.8rem; }
.week-table td { padding:8px; text-align:center; border-bottom:1px solid #eee; }
.week-balance { padding:10px; font-weight:700; display:flex; justify-content:space-between; }
.week-balance.positive { background:#ecfdf5; color:#10b981; }
.week-balance.negative { background:#fef2f2; color:#ef4444; }

/* ── Département ── */
.dept-banner { background:linear-gradient(135deg,#0f6b7c,#00c9a7); border-radius:12px; padding:20px 24px; margin-bottom:20px; color:white; display:flex; align-items:center; gap:16px; }
.dept-icon { width:52px; height:52px; background:rgba(255,255,255,.2); border-radius:12px; display:flex; align-items:center; justify-content:center; font-size:1.5rem; }
.dept-title { font-size:1.25rem; font-weight:800; }
.dept-subtitle { font-size:.85rem; opacity:.85; }

.employes-table-wrap { overflow-x:auto; }
.employes-table { width:100%; border-collapse:collapse; font-size:.85rem; }
.employes-table th { background:#f8fafc; padding:10px 12px; font-size:.65rem; text-transform:uppercase; text-align:center; white-space:nowrap; }
.employes-table th:first-child { text-align:left; }
.employes-table td { padding:10px 12px; border-bottom:1px solid #f1f5f9; text-align:center; }
.employes-table td:first-child { text-align:left; }
.employes-table tbody tr:hover { background:#f8fafc; }
.emp-mini-avatar { width:32px; height:32px; border-radius:50%; background:linear-gradient(135deg,#0f6b7c,#00c9a7); display:inline-flex; align-items:center; justify-content:center; font-size:.7rem; font-weight:700; color:white; margin-right:8px; }
.taux-bar { width:80px; height:6px; background:#e2e8f0; border-radius:3px; display:inline-block; vertical-align:middle; margin-right:6px; }
.taux-fill { height:100%; border-radius:3px; background:#0f6b7c; }
.taux-fill.warning { background:#f59e0b; }
.taux-fill.danger  { background:#ef4444; }
.btn-emp-detail { font-size:.75rem; padding:4px 10px; background:#f1f5f9; border:none; border-radius:6px; cursor:pointer; text-decoration:none; color:#0f6b7c; font-weight:600; }
.btn-emp-detail:hover { background:#e2e8f0; }

.filters-bar { display:flex; gap:10px; margin-bottom:20px; flex-wrap:wrap; align-items:center; }
.filters-bar select, .filters-bar input { padding:8px 12px; border:1px solid #e2e8f0; border-radius:6px; }
.period-nav { display:flex; gap:8px; align-items:center; margin-left:auto; }
.period-nav a { padding:8px 12px; background:#f1f5f9; border-radius:6px; text-decoration:none; color:#333; font-size:.85rem; }
.period-current { padding:8px 16px; background:#0f6b7c; color:white; border-radius:6px; font-weight:600; }

@media(max-width:1024px){
    .stats-grid { grid-template-columns:repeat(2,1fr); }
    .content-grid { grid-template-columns:1fr; }
    .employee-meta { margin-left:0; margin-top:12px; }
}
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>


<div class="filters-bar">
    <form method="GET" action="<?php echo e(route('temps.vue-ensemble')); ?>" style="display:flex;gap:10px;flex-wrap:wrap;align-items:center;flex:1;">

        <select name="employee_id" onchange="this.form.submit()" style="min-width:180px;">
            <option value="">Sélection employé...</option>
            <?php $__currentLoopData = $listeEmployesSelect; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $emp): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <option value="<?php echo e($emp->id); ?>" <?php echo e(($employeeId ?? '') == $emp->id ? 'selected' : ''); ?>>
                    <?php echo e($emp->first_name); ?> <?php echo e($emp->last_name); ?>

                </option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </select>

        <select name="department" onchange="this.form.submit()" style="min-width:180px;">
            <option value="">Tous les départements</option>
            <?php $__currentLoopData = $departments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $dept): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <option value="<?php echo e($dept); ?>" <?php echo e(($department ?? '') == $dept ? 'selected' : ''); ?>><?php echo e($dept); ?></option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </select>

        <input type="number" name="annee" value="<?php echo e($annee); ?>" min="2020" max="2030" style="width:80px;">
        <input type="hidden" name="mois" value="<?php echo e($mois); ?>">
        <button type="submit" class="btn btn-primary btn-sm">Rechercher</button>
    </form>

    <div class="period-nav">
        <a href="<?php echo e(route('temps.vue-ensemble', ['mois' => $moisPrecedent->month, 'annee' => $moisPrecedent->year, 'employee_id' => $employeeId ?? '', 'department' => $department ?? ''])); ?>">&larr; Préc.</a>
      <span class="period-current">
    <?php echo e(\Carbon\Carbon::create($annee, $mois, 1)->locale('fr')->translatedFormat('F Y')); ?>

</span>
        <a href="<?php echo e(route('temps.vue-ensemble', ['mois' => $moisSuivant->month, 'annee' => $moisSuivant->year, 'employee_id' => $employeeId ?? '', 'department' => $department ?? ''])); ?>">Suiv. &rarr;</a>
    </div>
</div>



<?php if($modeDepartement && $statsGlobalesDept): ?>


<div class="dept-banner">
    <div class="dept-icon">🏥</div>
    <div>
        <div class="dept-title">Département : <?php echo e($nomDepartement); ?></div>
        <div class="dept-subtitle"><?php echo e($statsGlobalesDept->nb_employes); ?> employé<?php echo e($statsGlobalesDept->nb_employes > 1 ? 's' : ''); ?> · <?php echo e(\Carbon\Carbon::create($annee, $mois, 1)->translatedFormat('F Y')); ?></div>
    </div>
</div>


<div class="stats-grid" style="grid-template-columns:repeat(5,1fr);">
    <div class="stat-card purple">
        <div class="stat-value" style="color:#8b5cf6;"><?php echo e($statsGlobalesDept->nb_employes); ?></div>
        <div class="stat-label">Employés</div>
    </div>
    <div class="stat-card primary">
        <div class="stat-value" style="color:#0f6b7c;"><?php echo e(number_format($statsGlobalesDept->heures_realisees, 1)); ?>h</div>
        <div class="stat-label">Heures réalisées</div>
    </div>
    <div class="stat-card info">
        <div class="stat-value"><?php echo e(number_format($statsGlobalesDept->heures_planifiees, 1)); ?>h</div>
        <div class="stat-label">Heures planifiées</div>
    </div>
    <div class="stat-card warning">
        <div class="stat-value" style="color:#f59e0b;"><?php echo e(number_format($statsGlobalesDept->heures_supplementaires, 1)); ?>h</div>
        <div class="stat-label">Heures supp.</div>
    </div>
    <div class="stat-card <?php echo e($statsGlobalesDept->ecart >= 0 ? 'success' : 'warning'); ?>">
        <div class="stat-value <?php echo e($statsGlobalesDept->ecart >= 0 ? 'positive' : 'negative'); ?>">
            <?php echo e($statsGlobalesDept->ecart >= 0 ? '+' : ''); ?><?php echo e(number_format($statsGlobalesDept->ecart, 1)); ?>h
        </div>
        <div class="stat-label">Écart global</div>
    </div>
</div>


<div class="content-grid">
    <div class="card">
        <div class="card-header">Évolution annuelle <?php echo e($annee); ?> — <?php echo e($nomDepartement); ?></div>
        <div class="card-body">
            <div class="chart-container">
                <canvas id="annualChartDept"></canvas>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">Récapitulatif du mois</div>
        <div class="card-body" style="padding:0;">
            <table class="data-table">
                <thead>
                    <tr><th>Indicateur</th><th>Valeur</th></tr>
                </thead>
                <tbody>
                    <tr><td>Heures planifiées</td><td><?php echo e(number_format($statsGlobalesDept->heures_planifiees, 1)); ?>h</td></tr>
                    <tr><td>Heures réalisées</td><td style="color:#0f6b7c;font-weight:700;"><?php echo e(number_format($statsGlobalesDept->heures_realisees, 1)); ?>h</td></tr>
                    <tr><td>Heures supplémentaires</td><td style="color:#f59e0b;font-weight:700;"><?php echo e(number_format($statsGlobalesDept->heures_supplementaires, 1)); ?>h</td></tr>
                    <tr><td>Taux de réalisation</td>
                        <td>
                            <div class="taux-bar"><div class="taux-fill <?php echo e($statsGlobalesDept->taux_realisation < 70 ? 'danger' : ($statsGlobalesDept->taux_realisation < 90 ? 'warning' : '')); ?>" style="width:<?php echo e(min($statsGlobalesDept->taux_realisation, 100)); ?>%"></div></div>
                            <?php echo e($statsGlobalesDept->taux_realisation); ?>%
                        </td>
                    </tr>
                    <tr class="total-row">
                        <td>Écart</td>
                        <td class="<?php echo e($statsGlobalesDept->ecart >= 0 ? 'positive' : 'negative'); ?>">
                            <?php echo e($statsGlobalesDept->ecart >= 0 ? '+' : ''); ?><?php echo e(number_format($statsGlobalesDept->ecart, 1)); ?>h
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>


<div style="font-weight:700;margin:20px 0 14px;">Compteurs hebdomadaires — <?php echo e($nomDepartement); ?></div>
<div class="weeks-grid">
    <?php $__currentLoopData = $semainerDept; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $semaine): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    <div class="week-card">
        <div class="week-header">
            <span>Semaine <?php echo e($semaine['numero']); ?></span>
            <span><?php echo e($semaine['debut']); ?> - <?php echo e($semaine['fin']); ?></span>
        </div>
        <table class="week-table">
            <tr><th>Plan</th><th>Réal</th><th>Solde</th></tr>
            <tr>
                <td><?php echo e($semaine['heures_planifiees']); ?>h</td>
                <td><?php echo e(number_format($semaine['heures_realisees'], 1)); ?>h</td>
                <td class="<?php echo e($semaine['solde'] >= 0 ? 'positive' : 'negative'); ?>">
                    <?php echo e($semaine['solde'] >= 0 ? '+' : ''); ?><?php echo e(number_format($semaine['solde'], 1)); ?>h
                </td>
            </tr>
        </table>
        <div class="week-balance <?php echo e($semaine['solde'] >= 0 ? 'positive' : 'negative'); ?>">
            <span>Solde</span>
            <span><?php echo e($semaine['solde'] >= 0 ? '+' : ''); ?><?php echo e(number_format($semaine['solde'], 1)); ?>h</span>
        </div>
    </div>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</div>


<div class="card" style="margin-top:20px;">
    <div class="card-header">
        <span>Détail par employé</span>
        <span style="font-size:.8rem;color:#64748b;font-weight:400;"><?php echo e($statsGlobalesDept->nb_employes); ?> employé(s)</span>
    </div>
    <div class="card-body" style="padding:0;">
        <div class="employes-table-wrap">
            <table class="employes-table">
                <thead>
                    <tr>
                        <th>Employé</th>
                        <th>Poste</th>
                        <th>Planifiées</th>
                        <th>Réalisées</th>
                        <th>Supp.</th>
                        <th>Écart</th>
                        <th>Taux</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__currentLoopData = $employesDept; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $emp): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <tr>
                        <td>
                            <div style="display:flex;align-items:center;">
                                <span class="emp-mini-avatar"><?php echo e($emp['initiales']); ?></span>
                                <span style="font-weight:600;"><?php echo e($emp['nom']); ?></span>
                            </div>
                        </td>
                        <td style="color:#64748b;"><?php echo e($emp['poste']); ?></td>
                        <td><?php echo e(number_format($emp['planifiees'], 1)); ?>h</td>
                        <td style="color:#0f6b7c;font-weight:700;"><?php echo e(number_format($emp['realisees'], 1)); ?>h</td>
                        <td style="color:#f59e0b;"><?php echo e(number_format($emp['supp'], 1)); ?>h</td>
                        <td class="<?php echo e($emp['ecart'] >= 0 ? 'positive' : 'negative'); ?>">
                            <?php echo e($emp['ecart'] >= 0 ? '+' : ''); ?><?php echo e(number_format($emp['ecart'], 1)); ?>h
                        </td>
                        <td>
                            <div style="display:flex;align-items:center;gap:4px;">
                                <div class="taux-bar">
                                    <div class="taux-fill <?php echo e($emp['taux'] < 70 ? 'danger' : ($emp['taux'] < 90 ? 'warning' : '')); ?>"
                                         style="width:<?php echo e(min($emp['taux'], 100)); ?>%"></div>
                                </div>
                                <span style="font-size:.75rem;"><?php echo e($emp['taux']); ?>%</span>
                            </div>
                        </td>
                        <td>
                            <a href="<?php echo e(route('temps.vue-ensemble', ['employee_id' => $emp['id'], 'annee' => $annee, 'mois' => $mois])); ?>"
                               class="btn-emp-detail">Détail →</a>
                        </td>
                    </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </tbody>
                
                <tfoot>
                    <tr class="total-row">
                        <td colspan="2" style="text-align:left;">Total département</td>
                        <td><?php echo e(number_format($statsGlobalesDept->heures_planifiees, 1)); ?>h</td>
                        <td style="color:#0f6b7c;"><?php echo e(number_format($statsGlobalesDept->heures_realisees, 1)); ?>h</td>
                        <td style="color:#f59e0b;"><?php echo e(number_format($statsGlobalesDept->heures_supplementaires, 1)); ?>h</td>
                        <td class="<?php echo e($statsGlobalesDept->ecart >= 0 ? 'positive' : 'negative'); ?>">
                            <?php echo e($statsGlobalesDept->ecart >= 0 ? '+' : ''); ?><?php echo e(number_format($statsGlobalesDept->ecart, 1)); ?>h
                        </td>
                        <td colspan="2"><?php echo e($statsGlobalesDept->taux_realisation); ?>%</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>


<?php elseif(!$modeDepartement && $employee && $employee->id): ?>


<div class="employee-card">
    <div class="employee-avatar">
        <?php echo e(strtoupper(substr($employee->first_name ?? 'U', 0, 1))); ?><?php echo e(strtoupper(substr($employee->last_name ?? 'U', 0, 1))); ?>

    </div>
    <div class="employee-info">
        <h3><?php echo e($employee->first_name); ?> <?php echo e($employee->last_name); ?></h3>
        <p><?php echo e($employee->position ?? 'Employé'); ?> | <?php echo e($employee->department ?? 'Service'); ?> | <?php echo e($employee->contract_type ?? 'CDI'); ?></p>
    </div>
    <div class="employee-meta">
        <div>
            <div class="meta-value"><?php echo e($employee->work_hours ?? 35); ?>h</div>
            <div class="meta-label">Heures/semaine</div>
        </div>
        <div>
            <div class="meta-value"><?php echo e(number_format($compteurMois->heures_planifiees ?? 0, 0)); ?>h</div>
            <div class="meta-label">Planning</div>
        </div>
    </div>
</div>


<div class="stats-grid">
    <div class="stat-card primary">
        <div class="stat-value" style="color:#0f6b7c;"><?php echo e(number_format($compteurMois->heures_realisees ?? 0, 1)); ?>h</div>
        <div class="stat-label">Heures réalisées</div>
    </div>
    <div class="stat-card info">
        <div class="stat-value"><?php echo e(number_format($compteurMois->heures_planifiees ?? 0, 1)); ?>h</div>
        <div class="stat-label">Heures planifiées</div>
    </div>
    <div class="stat-card warning">
        <div class="stat-value" style="color:#f59e0b;"><?php echo e(number_format($compteurMois->heures_supplementaires ?? 0, 1)); ?>h</div>
        <div class="stat-label">Heures supp.</div>
    </div>
    <div class="stat-card <?php echo e(($compteurMois->ecart ?? 0) >= 0 ? 'success' : 'warning'); ?>">
        <div class="stat-value <?php echo e(($compteurMois->ecart ?? 0) >= 0 ? 'positive' : 'negative'); ?>">
            <?php echo e(($compteurMois->ecart ?? 0) >= 0 ? '+' : ''); ?><?php echo e(number_format($compteurMois->ecart ?? 0, 1)); ?>h
        </div>
        <div class="stat-label">Écart</div>
    </div>
</div>


<div class="content-grid">
    <div class="card">
        <div class="card-header">Heures travaillées par jour</div>
        <div class="card-body">
            <div class="chart-container"><canvas id="dailyChart"></canvas></div>
        </div>
    </div>
    <div class="card">
        <div class="card-header">Récapitulatif du mois</div>
        <div class="card-body" style="padding:0;">
            <table class="data-table">
                <thead><tr><th>Type</th><th>Plan.</th><th>Réalisé</th><th>Écart</th></tr></thead>
                <tbody>
                    <tr>
                        <td>Travaillées</td>
                        <td><?php echo e(number_format($compteurMois->heures_planifiees ?? 0, 1)); ?>h</td>
                        <td style="font-weight:700;color:#0f6b7c;"><?php echo e(number_format($compteurMois->heures_realisees ?? 0, 1)); ?>h</td>
                        <td class="<?php echo e((($compteurMois->heures_realisees ?? 0) - ($compteurMois->heures_planifiees ?? 0)) >= 0 ? 'positive' : 'negative'); ?>">
                            <?php echo e((($compteurMois->heures_realisees ?? 0) - ($compteurMois->heures_planifiees ?? 0)) >= 0 ? '+' : ''); ?><?php echo e(number_format(($compteurMois->heures_realisees ?? 0) - ($compteurMois->heures_planifiees ?? 0), 1)); ?>h
                        </td>
                    </tr>
                    <tr><td>Non trav.</td><td>0h</td><td>0h</td><td>0h</td></tr>
                    <tr class="total-row">
                        <td>Total</td>
                        <td><?php echo e(number_format($compteurMois->heures_planifiees ?? 0, 1)); ?>h</td>
                        <td><?php echo e(number_format(($compteurMois->heures_realisees ?? 0) + ($compteurMois->heures_supplementaires ?? 0), 1)); ?>h</td>
                        <td class="<?php echo e(($compteurMois->ecart ?? 0) >= 0 ? 'positive' : 'negative'); ?>">
                            <?php echo e(($compteurMois->ecart ?? 0) >= 0 ? '+' : ''); ?><?php echo e(number_format($compteurMois->ecart ?? 0, 1)); ?>h
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>


<div style="font-weight:700;margin:20px 0 14px;">Compteurs hebdomadaires</div>
<div class="weeks-grid">
    <?php $__currentLoopData = $semaines; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $semaine): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    <div class="week-card">
        <div class="week-header">
            <span>Semaine <?php echo e($semaine['numero']); ?></span>
            <span><?php echo e($semaine['debut']); ?> - <?php echo e($semaine['fin']); ?></span>
        </div>
        <table class="week-table">
            <tr><th>Plan</th><th>Réal</th><th>Solde</th></tr>
            <tr>
                <td><?php echo e($semaine['heures_planifiees']); ?>h</td>
                <td><?php echo e(number_format($semaine['heures_realisees'], 1)); ?>h</td>
                <td class="<?php echo e($semaine['solde'] >= 0 ? 'positive' : 'negative'); ?>">
                    <?php echo e($semaine['solde'] >= 0 ? '+' : ''); ?><?php echo e(number_format($semaine['solde'], 1)); ?>h
                </td>
            </tr>
        </table>
        <div class="week-balance <?php echo e($semaine['solde'] >= 0 ? 'positive' : 'negative'); ?>">
            <span>Solde</span>
            <span><?php echo e($semaine['solde'] >= 0 ? '+' : ''); ?><?php echo e(number_format($semaine['solde'], 1)); ?>h</span>
        </div>
    </div>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</div>


<div class="card" style="margin-top:20px;">
    <div class="card-header">Évolution annuelle <?php echo e($annee); ?></div>
    <div class="card-body">
        <div style="height:260px;"><canvas id="annualChart"></canvas></div>
    </div>
</div>

<?php else: ?>

<div class="card">
    <div class="card-body" style="text-align:center;padding:40px;">
        <div style="font-size:2rem;margin-bottom:12px;">👆</div>
        <div style="font-size:1.1rem;margin-bottom:8px;font-weight:600;">Sélectionnez un employé ou un département</div>
        <div style="color:#64748b;">Utilisez les filtres ci-dessus pour afficher les données.</div>
    </div>
</div>
<?php endif; ?>

<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {

    // ── Graphique quotidien (mode employé) ──────────────────────────────────
    const joursDetails = <?php echo json_encode($joursDetails ?? [], 15, 512) ?>;
    const dailyCtx = document.getElementById('dailyChart');
    if (dailyCtx && joursDetails.length > 0) {
        const joursHeures = joursDetails.map(d => d.total);
        new Chart(dailyCtx, {
            type: 'bar',
            data: {
                labels: joursDetails.map(d => d.jour),
                datasets: [{
                    label: 'Heures',
                    data: joursHeures,
                    backgroundColor: joursHeures.map(h => h > 8 ? '#10b981' : h > 0 ? '#0f6b7c' : '#e2e8f0'),
                    borderRadius: 4,
                }]
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    y: { beginAtZero: true, max: 12 },
                    x: { title: { display: true, text: 'Jours du mois' } }
                }
            }
        });
    }

    // ── Graphique annuel employé ────────────────────────────────────────────
    const annualData = <?php echo json_encode($graphiqueMois ?? [], 15, 512) ?>;
    const annualCtx = document.getElementById('annualChart');
    if (annualCtx && annualData.length > 0) {
        new Chart(annualCtx, {
            type: 'bar',
            data: {
                labels: annualData.map(d => d.mois),
                datasets: [
                    { label: 'Heures réalisées', data: annualData.map(d => d.heures_realisees), backgroundColor: '#0f6b7c', borderRadius: 4 },
                    { label: 'Planning',          data: annualData.map(d => d.heures_planifiees), backgroundColor: '#e2e8f0', borderRadius: 4 }
                ]
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                plugins: { legend: { position: 'bottom' } },
                scales: { y: { beginAtZero: true } }
            }
        });
    }

    // ── Graphique annuel département ────────────────────────────────────────
    const annualDeptData = <?php echo json_encode($graphiqueMoisDept ?? [], 15, 512) ?>;
    const annualDeptCtx = document.getElementById('annualChartDept');
    if (annualDeptCtx && annualDeptData.length > 0) {
        new Chart(annualDeptCtx, {
            type: 'bar',
            data: {
                labels: annualDeptData.map(d => d.mois),
                datasets: [
                    { label: 'Heures réalisées', data: annualDeptData.map(d => d.heures_realisees), backgroundColor: '#0f6b7c', borderRadius: 4 },
                    { label: 'Planning',          data: annualDeptData.map(d => d.heures_planifiees), backgroundColor: '#e2e8f0', borderRadius: 4 },
                    { label: 'Heures supp.',      data: annualDeptData.map(d => d.heures_supp),       backgroundColor: '#f59e0b', borderRadius: 4 }
                ]
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                plugins: { legend: { position: 'bottom' } },
                scales: { y: { beginAtZero: true } }
            }
        });
    }
});
</script>
<?php $__env->stopPush(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\Projects\HospitalRh\resources\views/vue-ensemble/index.blade.php ENDPATH**/ ?>