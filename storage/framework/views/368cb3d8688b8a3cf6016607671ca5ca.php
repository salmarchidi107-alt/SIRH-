<?php $__env->startSection('title', 'Rapport RH'); ?>
<?php $__env->startSection('page-title', 'Rapport RH'); ?>

<?php $__env->startPush('styles'); ?>
<style>
/* ══════════════════════════════════════════════════
   REPORTING — thème projet
══════════════════════════════════════════════════ */

/* ── Filtres ── */
.rpt-filters {
    display: flex;
    flex-wrap: wrap;
    gap: 12px;
    align-items: flex-end;
    background: white;
    border: 1px solid var(--border, #e5e7eb);
    border-radius: 12px;
    padding: 16px 20px;
    margin-bottom: 24px;
}
.rpt-filter-group {
    display: flex;
    flex-direction: column;
    gap: 5px;
    min-width: 140px;
}
.rpt-filter-group label {
    font-size: 0.75rem;
    font-weight: 600;
    color: var(--text-muted, #6b7280);
    text-transform: uppercase;
    letter-spacing: .04em;
}
.rpt-filter-group select,
.rpt-filter-group input {
    padding: 8px 12px;
    border: 1px solid var(--border, #e5e7eb);
    border-radius: 8px;
    font-size: 0.85rem;
    font-family: inherit;
    background: white;
    color: var(--text, #111827);
    outline: none;
    transition: border-color .2s;
}
.rpt-filter-group select:focus,
.rpt-filter-group input:focus {
    border-color: var(--primary, #0ea5e9);
    box-shadow: 0 0 0 3px rgba(14,165,233,.1);
}
.rpt-period-pills {
    display: flex;
    gap: 4px;
    flex-wrap: wrap;
}
.rpt-period-pill {
    padding: 7px 14px;
    border: 1px solid var(--border, #e5e7eb);
    border-radius: 8px;
    font-size: 0.8rem;
    font-weight: 600;
    cursor: pointer;
    background: white;
    color: var(--text-muted, #6b7280);
    text-decoration: none;
    transition: all .15s;
}
.rpt-period-pill.active,
.rpt-period-pill:hover {
    background: var(--primary, #0ea5e9);
    color: white;
    border-color: var(--primary, #0ea5e9);
}
.btn-rpt-apply {
    padding: 9px 20px;
    background: linear-gradient(135deg, #2dd4bf, #0f766e);
    color: white;
    border: none;
    border-radius: 8px;
    font-size: 0.875rem;
    font-weight: 700;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 6px;
    font-family: inherit;
    transition: all .2s;
    box-shadow: 0 4px 12px rgba(13,166,116,.25);
}
.btn-rpt-apply:hover { transform: translateY(-1px); box-shadow: 0 6px 16px rgba(13,166,116,.35); }
.btn-rpt-pdf {
    padding: 9px 20px;
    background: white;
    color: #ef4444;
    border: 1px solid #fecaca;
    border-radius: 8px;
    font-size: 10px;
    font-weight: 700;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 6px;
    font-family: inherit;
    text-decoration: none;
    transition: all .2s;
}
.btn-rpt-pdf:hover { background: #fef2f2; }

/* ── Section header ── */
.rpt-section-header {
    display: flex;
    align-items: center;
    gap: 10px;
    margin: 28px 0 16px;
    padding-bottom: 12px;
    border-bottom: 2px solid var(--border, #e5e7eb);
}
.rpt-section-header h2 {
    font-size: 0.95rem;
    font-weight: 700;
    color: var(--text, #111827);
    margin: 0;
}
.rpt-section-header span.sub {
    font-size: 0.78rem;
    color: var(--text-muted, #6b7280);
    font-weight: 400;
}

/* ── Grille de listes ── */
.rpt-grid-2 {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 16px;
    margin-bottom: 16px;
}
.rpt-grid-4 {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 16px;
    margin-bottom: 16px;
}

/* ── Carte liste ── */
.rpt-list-card {
    background: white;
    border: 1px solid var(--border, #e5e7eb);
    border-radius: 12px;
    overflow: hidden;
}
.rpt-list-card-header {
    padding: 14px 18px;
    border-bottom: 1px solid var(--border, #e5e7eb);
    display: flex;
    align-items: center;
    justify-content: space-between;
    background: #f9fafb;
}
.rpt-list-card-header h3 {
    font-size: 0.82rem;
    font-weight: 700;
    color: var(--text, #111827);
    margin: 0;
    display: flex;
    align-items: center;
    gap: 7px;
}
.rpt-list-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 11px 18px;
    border-bottom: 1px solid var(--border, #f3f4f6);
    font-size: 0.875rem;
}
.rpt-list-row:last-child { border-bottom: none; }
.rpt-list-row:hover { background: #f9fafb; }
.rpt-list-label { color: var(--text-muted, #6b7280); }
.rpt-list-value { font-weight: 700; color: var(--text, #111827); font-size: 0.875rem; }
.rpt-list-row-head {
    display: flex;
    padding: 8px 18px;
    border-bottom: 1px solid var(--border, #e5e7eb);
    background: #f9fafb;
}
.rpt-list-row-head span {
    font-size: 0.72rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .04em;
    color: var(--text-muted, #6b7280);
}

/* ── Badges ── */
.badge-ok    { display: inline-flex; align-items: center; padding: 2px 9px; border-radius: 20px; font-size: 0.72rem; font-weight: 700; background: #dcfce7; color: #15803d; }
.badge-warn  { display: inline-flex; align-items: center; padding: 2px 9px; border-radius: 20px; font-size: 0.72rem; font-weight: 700; background: #fef3c7; color: #d97706; }
.badge-bad   { display: inline-flex; align-items: center; padding: 2px 9px; border-radius: 20px; font-size: 0.72rem; font-weight: 700; background: #fee2e2; color: #b91c1c; }
.badge-blue  { display: inline-flex; align-items: center; padding: 2px 9px; border-radius: 20px; font-size: 0.72rem; font-weight: 700; background: #e0f2fe; color: #0369a1; }
.badge-teal  { display: inline-flex; align-items: center; padding: 2px 9px; border-radius: 20px; font-size: 0.72rem; font-weight: 700; background: #ccfbf1; color: #0f766e; }
.badge-purple{ display: inline-flex; align-items: center; padding: 2px 9px; border-radius: 20px; font-size: 0.72rem; font-weight: 700; background: #ede9fe; color: #6d28d9; }
.badge-gray  { display: inline-flex; align-items: center; padding: 2px 9px; border-radius: 20px; font-size: 0.72rem; font-weight: 700; background: #f3f4f6; color: #4b5563; }

/* ── Alerte ── */
.rpt-alert {
    padding: 10px 16px;
    border-radius: 8px;
    font-size: 0.82rem;
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    gap: 10px;
    font-weight: 500;
}
.rpt-alert.warn { background: #fef3c7; border: 1px solid #fde68a; color: #92400e; }
.rpt-alert.ok   { background: #f0fdf4; border: 1px solid #bbf7d0; color: #15803d; }

/* ── Période info ── */
.rpt-period-info {
    font-size: 0.82rem;
    color: var(--text-muted, #6b7280);
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    gap: 6px;
}
.rpt-period-info strong { color: var(--text, #111827); font-weight: 600; }

/* ── Mini bar ── */
.mini-bar-wrap { display: flex; align-items: center; gap: 8px; }
.mini-bar { flex: 1; height: 6px; background: #f3f4f6; border-radius: 3px; overflow: hidden; }
.mini-bar-fill { height: 100%; border-radius: 3px; transition: width .8s ease; }
.mini-bar-pct { font-size: 0.72rem; color: var(--text-muted, #6b7280); width: 35px; text-align: right; font-weight: 600; }

/* ── Tableau absences ── */
.rpt-table { width: 100%; border-collapse: collapse; font-size: 0.85rem; }
.rpt-table th {
    padding: 10px 16px; text-align: left;
    background: #f9fafb;
    border-bottom: 2px solid var(--border, #e5e7eb);
    font-size: 0.73rem; font-weight: 700;
    text-transform: uppercase; letter-spacing: .04em;
    color: var(--text-muted, #6b7280);
}
.rpt-table td {
    padding: 10px 16px;
    border-bottom: 1px solid var(--border, #f3f4f6);
    color: var(--text, #111827);
}
.rpt-table tr:last-child td { border-bottom: none; }
.rpt-table tr:hover td { background: #f9fafb; }

/* ── Récap financier highlight row ── */
.rpt-table tr.highlight-row td { background: #f0f9ff; font-weight: 700; }
.rpt-table tr.garde-row td { background: #faf5ff; }

/* ── Responsive ── */
@media (max-width: 900px) {
    .rpt-grid-2 { grid-template-columns: 1fr; }
    .rpt-grid-4 { grid-template-columns: 1fr 1fr; }
}
@media (max-width: 600px) {
    .rpt-grid-4 { grid-template-columns: 1fr; }
    .rpt-filters { flex-direction: column; }
}
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
<div class="page-header">
    <div class="page-header-left">
        <h1>Rapport</h1>
        <p>Indicateurs opérationnels et financiers</p>
    </div>
</div>


<form method="GET" action="<?php echo e(route('reporting.index')); ?>" id="rptForm">
    <div class="rpt-filters">

        <div class="rpt-filter-group">
            <label>Période</label>
            <div class="rpt-period-pills">
                <?php $__currentLoopData = ['month' => 'Ce mois', 'quarter' => 'Trimestre', 'year' => 'Année', 'custom' => 'Période']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $val => $lbl): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <a href="#" class="rpt-period-pill <?php echo e($periode === $val ? 'active' : ''); ?>"
                       onclick="setPeriode('<?php echo e($val); ?>'); return false;"><?php echo e($lbl); ?></a>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
            <input type="hidden" name="periode" id="periodeInput" value="<?php echo e($periode); ?>">
        </div>

        <div class="rpt-filter-group" id="date-debut" style="<?php echo e($periode !== 'custom' ? 'display:none' : ''); ?>">
            <label>Du</label>
            <input type="date" name="date_debut" value="<?php echo e($dateDebut ?? $startDate->format('Y-m-d')); ?>">
        </div>
        <div class="rpt-filter-group" id="date-fin" style="<?php echo e($periode !== 'custom' ? 'display:none' : ''); ?>">
            <label>Au</label>
            <input type="date" name="date_fin" value="<?php echo e($dateFin ?? $endDate->format('Y-m-d')); ?>">
        </div>

        <div class="rpt-filter-group">
            <label>Département</label>
            <select name="departement">
                <option value="all" <?php echo e($departement === 'all' ? 'selected' : ''); ?>>Tous les départements</option>
                <?php $__currentLoopData = $departments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $dept): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($dept->id); ?>" <?php echo e($departement == $dept->id ? 'selected' : ''); ?>><?php echo e($dept->name); ?></option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
        </div>

        <button type="submit" class="btn-rpt-apply">
            Appliquer
        </button>

        <a href="<?php echo e(route('reporting.export-pdf', request()->query())); ?>" class="btn-rpt-pdf" target="_blank">
            Export PDF
        </a>
    </div>
</form>

<p class="rpt-period-info">

    Période analysée :
    <strong><?php echo e($startDate->locale('fr')->translatedFormat('d M Y')); ?></strong>
    &rarr;
    <strong><?php echo e($endDate->locale('fr')->translatedFormat('d M Y')); ?></strong>
    &nbsp;·&nbsp; <?php echo e($joursOuvrables); ?> jours ouvrables
</p>

<?php if($tauxAbsenteisme > 5): ?>
<div class="rpt-alert warn">
    Taux d'absentéisme à <?php echo e($tauxAbsenteisme); ?>% — seuil critique dépassé (5%). Intervention recommandée.
</div>
<?php endif; ?>


<div class="rpt-section-header">
    <div class="rpt-section-icon" style="background:#e0f2fe">
    </div>
    <div>
        <h2>Indicateurs Opérationnels</h2>
        <span class="sub">Effectifs · Absences · Temps de travail</span>
    </div>
</div>

<div class="rpt-grid-2">

    
    <div class="rpt-list-card">
        <div class="rpt-list-card-header">
            <h3>
                Effectifs &amp; présences
            </h3>
        </div>
        <div class="rpt-list-row">
            <span class="rpt-list-label">Salariés actifs</span>
            <span class="rpt-list-value"><?php echo e(number_format($nbrSalaries)); ?></span>
        </div>
        <div class="rpt-list-row">
            <span class="rpt-list-label">Présences</span>
            <div style="display:flex;align-items:center;gap:8px">
                <span class="rpt-list-value"><?php echo e(number_format($nbrSalaries - $nbrAbsences)); ?></span>
                <span class="badge-ok"><?php echo e($tauxPresence); ?>%</span>
            </div>
        </div>
        <div class="rpt-list-row">
            <span class="rpt-list-label">Absences approuvées</span>
            <div style="display:flex;align-items:center;gap:8px">
                <span class="rpt-list-value"><?php echo e(number_format($nbrAbsences)); ?></span>
                <span class="<?php echo e($tauxAbsenteisme > 5 ? 'badge-bad' : 'badge-warn'); ?>"><?php echo e($tauxAbsenteisme); ?>%</span>
            </div>
        </div>
        <div class="rpt-list-row">
            <span class="rpt-list-label">Jours d'absence</span>
            <span class="rpt-list-value"><?php echo e($joursAbsence); ?> j</span>
        </div>
        <div class="rpt-list-row">
            <span class="rpt-list-label">Employés sans planning</span>
            <span class="rpt-list-value" style="color:<?php echo e($empSansPlanning > 0 ? '#ef4444' : '#15803d'); ?>"><?php echo e($empSansPlanning); ?></span>
        </div>
    </div>

    
    <div class="rpt-list-card">
        <div class="rpt-list-card-header">
            <h3>
                Temps de travail
            </h3>
        </div>
        <div class="rpt-list-row">
            <span class="rpt-list-label">Heures planifiées</span>
            <span class="rpt-list-value" style="font-family:monospace"><?php echo e(number_format($heurePlanifiees, 0)); ?> h</span>
        </div>
        <div class="rpt-list-row">
            <span class="rpt-list-label">Heures pointées</span>
            <span class="rpt-list-value" style="font-family:monospace"><?php echo e(number_format($heuresPointees, 0)); ?> h</span>
        </div>
        <div class="rpt-list-row">
            <span class="rpt-list-label">Heures supplémentaires</span>
            <div style="display:flex;align-items:center;gap:8px">
                <span class="rpt-list-value" style="font-family:monospace;color:<?php echo e($heuresSupp > 0 ? '#15803d' : '#6b7280'); ?>">+<?php echo e(number_format($heuresSupp, 0)); ?> h</span>
                <?php if($heuresSupp > 0): ?><span class="badge-ok">Surplus</span><?php endif; ?>
            </div>
        </div>
        
        <div class="rpt-list-row" style="background:<?php echo e($heuresGarde > 0 ? '#faf5ff' : 'transparent'); ?>">
            <span class="rpt-list-label" style="display:flex;align-items:center;gap:6px">
                Heures de garde
            </span>
            <div style="display:flex;align-items:center;gap:8px">
                <span class="rpt-list-value" style="font-family:monospace;color:<?php echo e($heuresGarde > 0 ? '#6d28d9' : '#6b7280'); ?>">
                    <?php echo e(number_format($heuresGarde, 1)); ?> h
                </span>
                <?php if($nbGardes > 0): ?>
                    <span class="badge-purple"><?php echo e($nbGardes); ?> garde(s)</span>
                <?php endif; ?>
            </div>
        </div>
        <div class="rpt-list-row">
            <span class="rpt-list-label">Taux de présence</span>
            <div class="mini-bar-wrap" style="flex:1;max-width:200px;margin-left:auto">
                <div class="mini-bar">
                    <div class="mini-bar-fill" style="width:<?php echo e(min($tauxPresence,100)); ?>%;background:<?php echo e($tauxPresence >= 90 ? '#22c55e' : ($tauxPresence >= 70 ? '#f59e0b' : '#ef4444')); ?>"></div>
                </div>
                <span class="mini-bar-pct" style="color:<?php echo e($tauxPresence >= 90 ? '#15803d' : ($tauxPresence >= 70 ? '#d97706' : '#b91c1c')); ?>"><?php echo e($tauxPresence); ?>%</span>
            </div>
        </div>
        <div class="rpt-list-row">
            <span class="rpt-list-label">Taux d'absentéisme</span>
            <div class="mini-bar-wrap" style="flex:1;max-width:200px;margin-left:auto">
                <div class="mini-bar">
                    <div class="mini-bar-fill" style="width:<?php echo e(min($tauxAbsenteisme * 5, 100)); ?>%;background:<?php echo e($tauxAbsenteisme > 5 ? '#ef4444' : '#f59e0b'); ?>"></div>
                </div>
                <span class="mini-bar-pct" style="color:<?php echo e($tauxAbsenteisme > 5 ? '#b91c1c' : '#d97706'); ?>"><?php echo e($tauxAbsenteisme); ?>%</span>
            </div>
        </div>
    </div>

</div>


<div class="rpt-list-card" style="margin-bottom:16px">
    <div class="rpt-list-card-header">
        <h3>
            Répartition par département
        </h3>
        <span style="font-size:0.78rem;color:var(--text-muted,#6b7280)"><?php echo e($nbrSalaries); ?> salariés au total</span>
    </div>
    <?php $__currentLoopData = $repartitionDept; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $dept): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    <?php $pct = $nbrSalaries > 0 ? round(($dept->total / $nbrSalaries) * 100) : 0; ?>
    <div class="rpt-list-row">
        <span class="rpt-list-label" style="font-weight:600;color:var(--text,#111827)"><?php echo e($dept->dept); ?></span>
        <div class="mini-bar-wrap" style="flex:1;max-width:260px;margin-left:24px">
            <div class="mini-bar">
                <div class="mini-bar-fill" style="width:<?php echo e($pct); ?>%;background:linear-gradient(90deg,#2dd4bf,#0ea5e9)"></div>
            </div>
            <span class="mini-bar-pct"><?php echo e($pct); ?>%</span>
        </div>
    </div>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</div>


<?php if($absencesParType->isNotEmpty()): ?>
<div class="rpt-list-card" style="margin-bottom:16px">
    <div class="rpt-list-card-header">
        <h3>
            Absences par type
        </h3>
    </div>
    <table class="rpt-table">
        <thead>
            <tr>
                <th>Type</th>
                <th style="text-align:right">Demandes</th>
                <th style="text-align:right">Jours totaux</th>
                <th style="text-align:right">Moy. j/demande</th>
            </tr>
        </thead>
        <tbody>
            <?php
                $typeColors = [
                    'conge_paye'  => 'badge-ok',
                    'maladie'     => 'badge-bad',
                    'sans_solde'  => 'badge-warn',
                    'maternite'   => 'badge-purple',
                    'autre'       => 'badge-gray',
                ];
            ?>
            <?php $__currentLoopData = $absencesParType; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $abs): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <tr>
                <td>
                    <span class="<?php echo e($typeColors[$abs->type] ?? 'badge-gray'); ?>">
                        <?php echo e(ucfirst(str_replace('_', ' ', $abs->type))); ?>

                    </span>
                </td>
                <td style="text-align:right;font-weight:700;font-family:monospace"><?php echo e($abs->count); ?></td>
                <td style="text-align:right;font-family:monospace"><?php echo e($abs->jours); ?> j</td>
                <td style="text-align:right;color:var(--text-muted,#6b7280);font-family:monospace">
                    <?php echo e($abs->count > 0 ? round($abs->jours / $abs->count, 1) : 0); ?> j
                </td>
            </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </tbody>
    </table>
</div>
<?php endif; ?>


<div class="rpt-section-header">
    <div class="rpt-section-icon" style="background:#dcfce7">
    </div>
    <div>
        <h2>Indicateurs Financiers</h2>
        <span class="sub">Paie · Charges · DGI · Bulletins · Gardes</span>
    </div>
</div>

<div class="rpt-grid-2" style="margin-bottom:16px">

    
    <div class="rpt-list-card">
        <div class="rpt-list-card-header">
            <h3>
                Masse salariale
            </h3>
        </div>
        <div class="rpt-list-row">
            <span class="rpt-list-label">Masse salariale brute</span>
            <span class="rpt-list-value" style="font-family:monospace"><?php echo e(number_format($masseSalarialeBrute, 2, ',', ' ')); ?> MAD</span>
        </div>
        <div class="rpt-list-row">
            <span class="rpt-list-label">Net à payer total</span>
            <span class="rpt-list-value" style="font-family:monospace;color:#15803d"><?php echo e(number_format($netTotal, 2, ',', ' ')); ?> MAD</span>
        </div>
        <div class="rpt-list-row">
            <span class="rpt-list-label">Coût employeur total</span>
            <span class="rpt-list-value" style="font-family:monospace"><?php echo e(number_format($coutEmployeur, 2, ',', ' ')); ?> MAD</span>
        </div>
        <div class="rpt-list-row">
            <span class="rpt-list-label">Salaire moyen brut</span>
            <span class="rpt-list-value" style="font-family:monospace"><?php echo e(number_format($salaireMoyenBrut, 2, ',', ' ')); ?> MAD</span>
        </div>
        <div class="rpt-list-row">
            <span class="rpt-list-label">Salaire moyen net</span>
            <span class="rpt-list-value" style="font-family:monospace;color:#0f766e"><?php echo e(number_format($salaireMoyenNet, 2, ',', ' ')); ?> MAD</span>
        </div>
        
        <div class="rpt-list-row" style="background:#faf5ff;border-top:1px solid #ede9fe">
            <span class="rpt-list-label" style="display:flex;align-items:center;gap:6px">
                Heures de garde (bulletins)
            </span>
            <span class="rpt-list-value" style="font-family:monospace;color:#6d28d9">
                <?php echo e(number_format($gardeHeures, 1)); ?> h
            </span>
        </div>
        <div class="rpt-list-row" style="background:#faf5ff">
            <span class="rpt-list-label" style="display:flex;align-items:center;gap:6px">
                Paiement gardes
            </span>
            <span class="rpt-list-value" style="font-family:monospace;color:#6d28d9;font-weight:700">
                <?php echo e(number_format($gardeTotal, 2, ',', ' ')); ?> MAD
            </span>
        </div>
    </div>

    
    <div class="rpt-list-card">
        <div class="rpt-list-card-header">
            <h3>
                Charges &amp; déclarations
            </h3>
        </div>
        <div class="rpt-list-row">
            <span class="rpt-list-label">CNSS salariale <span class="badge-blue">4.48%</span></span>
            <span class="rpt-list-value" style="font-family:monospace"><?php echo e(number_format($cnssEmployee, 2, ',', ' ')); ?> MAD</span>
        </div>
        <div class="rpt-list-row">
            <span class="rpt-list-label">CNSS patronale <span class="badge-blue">8.98%</span></span>
            <span class="rpt-list-value" style="font-family:monospace"><?php echo e(number_format($cnssPatron, 2, ',', ' ')); ?> MAD</span>
        </div>
        <div class="rpt-list-row">
            <span class="rpt-list-label">AMO salariale <span class="badge-teal">2.26%</span></span>
            <span class="rpt-list-value" style="font-family:monospace"><?php echo e(number_format($amoEmployee, 2, ',', ' ')); ?> MAD</span>
        </div>
        <div class="rpt-list-row">
            <span class="rpt-list-label">AMO patronale <span class="badge-teal">2.26%</span></span>
            <span class="rpt-list-value" style="font-family:monospace"><?php echo e(number_format($amoPatron, 2, ',', ' ')); ?> MAD</span>
        </div>
        <div class="rpt-list-row">
            <span class="rpt-list-label">IR retenu à la source</span>
            <span class="rpt-list-value" style="font-family:monospace;color:#b91c1c"><?php echo e(number_format($irRetenu, 2, ',', ' ')); ?> MAD</span>
        </div>
        <div class="rpt-list-row">
            <span class="rpt-list-label">DGI mensuelle <span class="badge-gray">IR + 1.6%</span></span>
            <span class="rpt-list-value" style="font-family:monospace;color:#b91c1c"><?php echo e(number_format($dgiMensuelle, 2, ',', ' ')); ?> MAD</span>
        </div>
    </div>

</div>


<div class="rpt-list-card" style="margin-bottom:16px">
    <div class="rpt-list-card-header">
        <h3>
            État des bulletins de paie
        </h3>
        <span class="<?php echo e($bulletinsTotal > 0 && round($bulletinsValides/$bulletinsTotal*100) == 100 ? 'badge-ok' : 'badge-warn'); ?>">
            <?php echo e($bulletinsTotal > 0 ? round($bulletinsValides / $bulletinsTotal * 100) : 0); ?>% validés
        </span>
    </div>
    <div class="rpt-list-row">
        <span class="rpt-list-label">Bulletins générés</span>
        <span class="rpt-list-value"><?php echo e($bulletinsTotal); ?></span>
    </div>
    <div class="rpt-list-row">
        <span class="rpt-list-label">Bulletins validés</span>
        <div style="display:flex;align-items:center;gap:10px;flex:1;max-width:300px;margin-left:auto">
            <div class="mini-bar" style="flex:1">
                <div class="mini-bar-fill" style="width:<?php echo e($bulletinsTotal > 0 ? round($bulletinsValides/$bulletinsTotal*100) : 0); ?>%;background:#22c55e"></div>
            </div>
            <span class="rpt-list-value" style="min-width:60px;text-align:right"><?php echo e($bulletinsValides); ?> / <?php echo e($bulletinsTotal); ?></span>
        </div>
    </div>
    <div class="rpt-list-row">
        <span class="rpt-list-label">En attente de validation</span>
        <span class="rpt-list-value" style="color:<?php echo e(($bulletinsTotal - $bulletinsValides) > 0 ? '#d97706' : '#15803d'); ?>">
            <?php echo e($bulletinsTotal - $bulletinsValides); ?>

        </span>
    </div>
</div>


<div class="rpt-list-card" style="margin-bottom:24px">
    <div class="rpt-list-card-header">
        <h3>
            Récapitulatif financier complet
        </h3>
    </div>
    <table class="rpt-table">
        <thead>
            <tr>
                <th>Indicateur</th>
                <th style="text-align:right">Montant (MAD)</th>
                <th style="text-align:right">% masse brute</th>
                <th>Nature</th>
            </tr>
        </thead>
        <tbody>
            <?php
                $rows = [
                    ['Masse salariale brute',       $masseSalarialeBrute, 100,                                                                      'Base',    'badge-gray',   false],
                    ['Net à payer total',            $netTotal,           $masseSalarialeBrute ? round($netTotal/$masseSalarialeBrute*100,1):0,       'Débit',   'badge-ok',     false],
                    ['CNSS salariale (4.48%)',       $cnssEmployee,       4.5,                                                                       'Retenue', 'badge-blue',   false],
                    ['AMO salariale (2.26%)',        $amoEmployee,        2.3,                                                                       'Retenue', 'badge-blue',   false],
                    ['IR retenu à la source',        $irRetenu,           $masseSalarialeBrute ? round($irRetenu/$masseSalarialeBrute*100,1):0,       'Retenue', 'badge-bad',    false],
                    ['CNSS patronale (8.98%)',       $cnssPatron,         9.0,                                                                       'Charge',  'badge-warn',   false],
                    ['AMO patronale (2.26%)',        $amoPatron,          2.3,                                                                       'Charge',  'badge-warn',   false],
                    ['Paiement gardes',              $gardeTotal,         $masseSalarialeBrute ? round($gardeTotal/$masseSalarialeBrute*100,1):0,     'Garde',   'badge-purple', true],
                    ['DGI — déclaration mensuelle',  $dgiMensuelle,       $masseSalarialeBrute ? round($dgiMensuelle/$masseSalarialeBrute*100,1):0,   'Fiscal',  'badge-purple', false],
                    ['Coût employeur total',         $coutEmployeur,      $masseSalarialeBrute ? round($coutEmployeur/$masseSalarialeBrute*100,1):0,  'Total',   'badge-teal',   false],
                ];
            ?>
            <?php $__currentLoopData = $rows; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as [$label, $montant, $pct, $nature, $badgeClass, $isGarde]): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <tr class="<?php echo e($label === 'Coût employeur total' ? 'highlight-row' : ($isGarde ? 'garde-row' : '')); ?>">
                <td style="font-weight:<?php echo e($label === 'Coût employeur total' ? '700' : '400'); ?>">
                    <?php if($isGarde): ?>
                        <span style="display:inline-flex;align-items:center;gap:5px">
                            <?php echo e($label); ?>

                            <span style="font-size:0.72rem;color:#8b5cf6;font-weight:400">(<?php echo e($gardeHeures); ?> h)</span>
                        </span>
                    <?php else: ?>
                        <?php echo e($label); ?>

                    <?php endif; ?>
                </td>
                <td style="text-align:right;font-family:monospace;font-weight:600;<?php echo e($isGarde ? 'color:#6d28d9' : ''); ?>">
                    <?php echo e(number_format($montant, 2, ',', ' ')); ?>

                </td>
                <td style="text-align:right">
                    <div class="mini-bar-wrap" style="justify-content:flex-end">
                        <span class="mini-bar-pct"><?php echo e($pct); ?>%</span>
                    </div>
                </td>
                <td><span class="<?php echo e($badgeClass); ?>"><?php echo e($nature); ?></span></td>
            </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </tbody>
    </table>
</div>

<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
function setPeriode(val) {
    document.getElementById('periodeInput').value = val;
    const show = val === 'custom';
    document.getElementById('date-debut').style.display = show ? '' : 'none';
    document.getElementById('date-fin').style.display   = show ? '' : 'none';
    document.querySelectorAll('.rpt-period-pill').forEach(p => {
        p.classList.toggle('active', p.textContent.trim() === document.querySelector('[onclick="setPeriode(\'' + val + '\'); return false;"]')?.textContent.trim());
    });
    if (!show) document.getElementById('rptForm').submit();
}

document.querySelectorAll('.mini-bar-fill').forEach(el => {
    const w = el.style.width;
    el.style.width = '0';
    setTimeout(() => { el.style.width = w; }, 250);
});
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\Projects\SIRH-\resources\views/reporting/index.blade.php ENDPATH**/ ?>