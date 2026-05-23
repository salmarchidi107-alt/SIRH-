<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<title>Rapport de Paie — <?php echo e($periodLabel); ?></title>
<style>
/* ═══════════════════════════════════════════════════
   RESET & BASE — Times New Roman 12pt professionnel
═══════════════════════════════════════════════════ */
* { margin:0; padding:0; box-sizing:border-box; }

@page {
    size: A4 portrait;
    margin: 16mm 18mm 16mm 18mm;
}

body {
    font-family: "Times New Roman", Times, serif;
    font-size: 12px;
    color: #1a1a1a;
    background: #ffffff;
    line-height: 1.6;
    width: 100%;
}

/* ═══════════════════════════════════════════════════
   HEADER
═══════════════════════════════════════════════════ */
.header {
    padding-bottom: 14px;
    border-bottom: 2.5px solid #0d9488;
    margin-bottom: 22px;
    display: table;
    width: 100%;
}
.header-left  { display: table-cell; vertical-align: middle; width: 62%; }
.header-right { display: table-cell; vertical-align: middle; text-align: right; width: 38%; }

.company-name {
    font-size: 13px;
    font-weight: bold;
    color: #0d9488;
    letter-spacing: 1.5px;
    text-transform: uppercase;
}
.doc-title {
    font-size: 22px;
    font-weight: bold;
    color: #111827;
    margin-top: 4px;
    letter-spacing: -0.3px;
}
.doc-sub {
    font-size: 11px;
    color: #6b7280;
    margin-top: 4px;
    font-style: italic;
}
.meta-pill {
    display: inline-block;
    background: #f0fdfa;
    border: 1px solid #99f6e4;
    color: #0d9488;
    font-size: 11px;
    font-weight: bold;
    padding: 4px 14px;
    border-radius: 20px;
    margin-bottom: 5px;
}
.meta-pill-blue {
    display: inline-block;
    background: #eff6ff;
    border: 1px solid #bfdbfe;
    color: #1d4ed8;
    font-size: 11px;
    font-weight: bold;
    padding: 4px 14px;
    border-radius: 20px;
    margin-bottom: 5px;
    margin-left: 4px;
}
.meta-line {
    font-size: 10px;
    color: #9ca3af;
    margin-top: 4px;
    font-style: italic;
}

/* ═══════════════════════════════════════════════════
   KPI CARDS
═══════════════════════════════════════════════════ */
.kpi-row {
    display: table;
    width: 100%;
    margin-bottom: 18px;
    border-collapse: separate;
    border-spacing: 6px 0;
}
.kpi-cell { display: table-cell; width: 25%; padding: 0 3px; }
.kpi-card {
    background: #f9fafb;
    border: 1px solid #e5e7eb;
    border-radius: 6px;
    padding: 10px 12px;
    border-left: 4px solid #0d9488;
}
.kpi-card.red   { border-left-color: #ef4444; }
.kpi-card.amber { border-left-color: #f59e0b; }
.kpi-card.blue  { border-left-color: #3b82f6; }
.kpi-label {
    font-size: 9px;
    color: #6b7280;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    font-weight: bold;
    font-family: "Times New Roman", Times, serif;
}
.kpi-value {
    font-size: 14px;
    font-weight: bold;
    color: #111827;
    margin-top: 4px;
    line-height: 1.2;
}
.kpi-sub {
    font-size: 9px;
    color: #9ca3af;
    margin-top: 4px;
    font-style: italic;
}

/* ═══════════════════════════════════════════════════
   SECTION TITLE
═══════════════════════════════════════════════════ */
.section-title {
    font-size: 11px;
    font-weight: bold;
    color: #111827;
    text-transform: uppercase;
    letter-spacing: 0.1em;
    padding: 6px 0 6px 0;
    border-bottom: 1.5px solid #0d9488;
    margin-bottom: 12px;
    display: table;
    width: 100%;
}
.section-title-text { display: table-cell; vertical-align: middle; }
.section-count {
    display: table-cell;
    text-align: right;
    vertical-align: middle;
    background: #e5e7eb;
    /* badge inline via span */
}
.section-count span {
    background: #ccfbf1;
    color: #0d9488;
    font-size: 9px;
    padding: 2px 9px;
    border-radius: 10px;
    font-weight: bold;
    letter-spacing: 0;
    text-transform: none;
}

/* ═══════════════════════════════════════════════════
   TABLE
═══════════════════════════════════════════════════ */
.main-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 10.5px;
    font-family: "Times New Roman", Times, serif;
}
.main-table thead tr {
    background: #0d9488;
    color: #ffffff;
}
.main-table thead th {
    padding: 8px 9px;
    text-align: left;
    font-size: 9px;
    font-weight: bold;
    text-transform: uppercase;
    letter-spacing: 0.06em;
    white-space: nowrap;
    font-family: "Times New Roman", Times, serif;
}
.main-table thead th.right  { text-align: right; }
.main-table thead th.center { text-align: center; }

.main-table tbody tr { border-bottom: 1px solid #e5e7eb; }
.main-table tbody tr:nth-child(even) td { background: #f9fafb; }
.main-table tbody tr:last-child td { border-bottom: none; }

.main-table tbody td {
    padding: 7px 9px;
    vertical-align: middle;
    color: #374151;
}
.main-table tbody td.right  { text-align: right; }
.main-table tbody td.center { text-align: center; }
.main-table tbody td.muted  { color: #9ca3af; }
.main-table tbody td.bold   { font-weight: bold; }
.main-table tbody td.green  { color: #047857; font-weight: bold; }
.main-table tbody td.red    { color: #dc2626; }

.emp-name { font-weight: bold; color: #111827; font-size: 11px; }
.emp-pos  { color: #9ca3af; font-size: 9.5px; margin-top: 2px; font-style: italic; }

/* ── Badges statut ── */
.badge {
    display: inline-block;
    padding: 3px 9px;
    border-radius: 10px;
    font-size: 9.5px;
    font-weight: bold;
    white-space: nowrap;
    font-family: "Times New Roman", Times, serif;
}
.badge-draft     { background: #fef3c7; color: #92400e; }
.badge-validated { background: #d1fae5; color: #065f46; }
.badge-paid      { background: #dbeafe; color: #1e40af; }
.badge-none      { background: #f3f4f6; color: #6b7280; }

/* ── Ligne totaux ── */
.total-row td {
    background: #f0fdfa !important;
    border-top: 2px solid #0d9488;
    border-bottom: 2px solid #0d9488;
    font-weight: bold;
    color: #111827;
    font-size: 11px;
    padding: 10px 10px;
}
.total-row td.green { color: #047857; }
.total-row td.red   { color: #dc2626; }

/* ═══════════════════════════════════════════════════
   RÉCAP CHARGES
═══════════════════════════════════════════════════ */
.charges-table {
    width: 55%;
    border-collapse: collapse;
    font-size: 11px;
    font-family: "Times New Roman", Times, serif;
    margin-top: 4px;
}
.charges-table thead tr { background: #1f2937; color: #fff; }
.charges-table thead th {
    padding: 8px 12px;
    text-align: left;
    font-size: 9.5px;
    font-weight: bold;
    text-transform: uppercase;
    letter-spacing: 0.06em;
}
.charges-table thead th.right { text-align: right; }
.charges-table tbody td {
    padding: 8px 12px;
    border-bottom: 1px solid #e5e7eb;
    color: #374151;
}
.charges-table tbody td.right { text-align: right; }
.charges-table tbody td.muted { color: #9ca3af; font-style: italic; }
.charges-table tbody td.red   { color: #dc2626; font-weight: bold; }
.charges-table .total-row td {
    background: #f0fdfa !important;
    border-top: 2px solid #0d9488;
    border-bottom: 2px solid #0d9488;
    font-weight: bold;
}
.charges-table .total-row td.green { color: #047857; }

/* ═══════════════════════════════════════════════════
   FOOTER
═══════════════════════════════════════════════════ */
.footer {
    margin-top: 30px;
    padding-top: 12px;
    border-top: 1px solid #d1d5db;
    display: table;
    width: 100%;
}
.footer-left  {
    display: table-cell; width: 50%;
    font-size: 10px; color: #6b7280; vertical-align: middle;
}
.footer-right {
    display: table-cell; width: 50%; text-align: right;
    font-size: 10px; color: #6b7280; vertical-align: middle;
}
.footer-legal {
    margin-top: 8px;
    font-size: 9px;
    color: #d1d5db;
    text-align: center;
    font-style: italic;
}

/* ─ Page break ─ */
.page-break { page-break-after: always; }
</style>
</head>
<body>


<div class="header">
    <div class="header-left">
        <?php if(!empty($tenant?->name)): ?>
        <div class="company-name"><?php echo e(strtoupper($tenant->name)); ?></div>
        <?php endif; ?>
        <div class="doc-title">Rapport de Paie</div>
        <div class="doc-sub">Récapitulatif des bulletins de salaire</div>
    </div>
    <div class="header-right">
        <div class="meta-pill"><?php echo e($periodLabel); ?></div>
        <?php if($department): ?>
        <div class="meta-pill-blue">Dept : <?php echo e($department); ?></div>
        <?php endif; ?>
        <div class="meta-line">
            Généré le <?php echo e(now()->locale('fr')->isoFormat('D MMMM YYYY [à] HH:mm')); ?>

        </div>
        <?php if($status): ?>
        <div class="meta-line">Filtre statut : <strong><?php echo e(ucfirst($status)); ?></strong></div>
        <?php endif; ?>
    </div>
</div>


<div class="kpi-row">
    <div class="kpi-cell">
        <div class="kpi-card">
            <div class="kpi-label">Masse salariale brute</div>
            <div class="kpi-value"><?php echo e(number_format($summary['total_gross'], 0, ',', ' ')); ?> MAD</div>
            <div class="kpi-sub">Coût employeur : <?php echo e(number_format($summary['total_employer_cost'] ?? 0, 0, ',', ' ')); ?> MAD</div>
        </div>
    </div>
    <div class="kpi-cell">
        <div class="kpi-card amber">
            <div class="kpi-label">Charges salariales</div>
            <div class="kpi-value"><?php echo e(number_format($summary['total_cnss_sal'] + $summary['total_amo_sal'], 0, ',', ' ')); ?> MAD</div>
            <div class="kpi-sub">CNSS : <?php echo e(number_format($summary['total_cnss_sal'], 0, ',', ' ')); ?> | AMO : <?php echo e(number_format($summary['total_amo_sal'], 0, ',', ' ')); ?></div>
        </div>
    </div>
    <div class="kpi-cell">
        <div class="kpi-card red">
            <div class="kpi-label">IR retenu à la source</div>
            <div class="kpi-value"><?php echo e(number_format($summary['total_ir'], 0, ',', ' ')); ?> MAD</div>
            <div class="kpi-sub">DGI — déclaration mensuelle</div>
        </div>
    </div>
    <div class="kpi-cell">
        <div class="kpi-card blue">
            <div class="kpi-label">Net à payer total</div>
            <div class="kpi-value"><?php echo e(number_format($summary['total_net'], 0, ',', ' ')); ?> MAD</div>
            <div class="kpi-sub"><?php echo e($summary['count_validated']); ?> validés / <?php echo e($summary['count']); ?> bulletins</div>
        </div>
    </div>
</div>


<div class="section-title">
    <div class="section-title-text">Détail des bulletins</div>
    <div class="section-count"><span><?php echo e($allEmployees->count()); ?> employés</span></div>
</div>

<table class="main-table">
    <thead>
        <tr>
            <th style="width:18%">Employé</th>
            <th style="width:12%">Département</th>
            <?php if($dateDebut && $dateFin): ?>
            <th style="width:10%" class="center">Période</th>
            <?php endif; ?>
            <th style="width:10%" class="center">Mode paiement</th>
            <th style="width:9%" class="right">Base</th>
            <th style="width:9%" class="right">Brut</th>
            <th style="width:9%" class="right">CNSS+AMO</th>
            <th style="width:8%" class="right">IR</th>
            <th style="width:10%" class="right">Net à payer</th>
            <th style="width:8%" class="center">Statut</th>
        </tr>
    </thead>
    <tbody>
    <?php $totBrut = 0; $totCnss = 0; $totIr = 0; $totNet = 0; ?>

    <?php $__empty_1 = true; $__currentLoopData = $allEmployees; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $emp): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
        <?php $salList = $emp->salaries; ?>
        <?php if($salList->isEmpty()): ?>
        <tr>
            <td>
                <div class="emp-name"><?php echo e($emp->full_name); ?></div>
                <div class="emp-pos"><?php echo e($emp->position); ?></div>
            </td>
            <td class="muted"><?php echo e($emp->department ?? '—'); ?></td>
            <?php if($dateDebut && $dateFin): ?><td class="center muted">—</td><?php endif; ?>
            <td class="center muted"><?php echo e(ucfirst($emp->payment_method ?? '—')); ?></td>
            <td class="right muted"><?php echo e(number_format($emp->base_salary, 0, ',', ' ')); ?></td>
            <td class="center muted">—</td>
            <td class="center muted">—</td>
            <td class="center muted">—</td>
            <td class="center muted">—</td>
            <td class="center"><span class="badge badge-none">Non généré</span></td>
        </tr>
        <?php else: ?>
            <?php $__currentLoopData = $salList; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $sal): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <?php
                $totBrut += $sal->gross_salary;
                $totCnss += $sal->cnss_deduction + $sal->amo_deduction;
                $totIr   += $sal->ir_deduction;
                $totNet  += $sal->net_salary;
                $badgeClass = match($sal->status) {
                    'validated' => 'badge-validated',
                    'paid'      => 'badge-paid',
                    'draft'     => 'badge-draft',
                    default     => 'badge-none',
                };
                $badgeLabel = match($sal->status) {
                    'validated' => 'Validé',
                    'paid'      => 'Rémunéré',
                    'draft'     => 'Brouillon',
                    default     => ucfirst($sal->status ?? 'Inconnu'),
                };
                $cur = $sal->currency ?? 'MAD';
            ?>
            <tr>
                <td>
                    <div class="emp-name"><?php echo e($emp->full_name); ?></div>
                    <div class="emp-pos"><?php echo e($emp->position); ?></div>
                </td>
                <td class="muted"><?php echo e($emp->department ?? '—'); ?></td>
                <?php if($dateDebut && $dateFin): ?>
                <td class="center muted" style="white-space:nowrap;">
                    <?php echo e(\Carbon\Carbon::create($sal->year, $sal->month)->locale('fr')->isoFormat('MMM YYYY')); ?>

                </td>
                <?php endif; ?>
                <td class="center muted" style="font-size:10.5px;">
                    <?php if($emp->payment_method == 'virement'): ?>
                        Virement<?php echo e($emp->bank ? ' '.$emp->bank : ''); ?>

                    <?php else: ?>
                        <?php echo e(ucfirst($emp->payment_method ?? '—')); ?>

                    <?php endif; ?>
                </td>
                <td class="right muted"><?php echo e(number_format($emp->base_salary, 0, ',', ' ')); ?></td>
                <td class="right bold"><?php echo e(number_format($sal->gross_salary, 0, ',', ' ')); ?></td>
                <td class="right red"><?php echo e(number_format($sal->cnss_deduction + $sal->amo_deduction, 0, ',', ' ')); ?></td>
                <td class="right red"><?php echo e(number_format($sal->ir_deduction, 0, ',', ' ')); ?></td>
                <td class="right green"><?php echo e(number_format($sal->net_salary, 0, ',', ' ')); ?></td>
                <td class="center"><span class="badge <?php echo e($badgeClass); ?>"><?php echo e($badgeLabel); ?></span></td>
            </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        <?php endif; ?>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
        <tr>
            <td colspan="<?php echo e(($dateDebut && $dateFin) ? 10 : 9); ?>"
                style="text-align:center;padding:24px;color:#9ca3af;font-style:italic;">
                Aucun bulletin trouvé pour cette période.
            </td>
        </tr>
    <?php endif; ?>

    <?php if($allEmployees->count() > 0): ?>
    <tr class="total-row">
        <td colspan="<?php echo e(($dateDebut && $dateFin) ? 5 : 4); ?>" style="text-align:left;">
            TOTAL — <?php echo e($summary['count']); ?> bulletins
        </td>
        <td class="right bold"><?php echo e(number_format($totBrut, 0, ',', ' ')); ?> MAD</td>
        <td class="right red"><?php echo e(number_format($totCnss, 0, ',', ' ')); ?> MAD</td>
        <td class="right red"><?php echo e(number_format($totIr, 0, ',', ' ')); ?> MAD</td>
        <td class="right green"><?php echo e(number_format($totNet, 0, ',', ' ')); ?> MAD</td>
        <td class="center" style="font-size:10px;color:#0d9488;font-weight:bold;">
            <?php echo e($summary['count_validated']); ?>V / <?php echo e($summary['count_paid']); ?>P / <?php echo e($summary['count_draft']); ?>B
        </td>
    </tr>
    <?php endif; ?>
    </tbody>
</table>


<?php if(($summary['total_employer_cost'] ?? 0) > 0): ?>
<div style="margin-top:24px;">
    <div class="section-title">
        <div class="section-title-text">Récapitulatif charges patronales</div>
    </div>
    <table class="charges-table">
        <thead>
            <tr>
                <th style="width:50%">Poste</th>
                <th style="width:20%">Taux</th>
                <th class="right" style="width:30%">Montant (MAD)</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>CNSS salariale</td>
                <td class="muted">4,48 %</td>
                <td class="right"><?php echo e(number_format($summary['total_cnss_sal'], 0, ',', ' ')); ?></td>
            </tr>
            <tr>
                <td>AMO salariale</td>
                <td class="muted">2,26 %</td>
                <td class="right"><?php echo e(number_format($summary['total_amo_sal'], 0, ',', ' ')); ?></td>
            </tr>
            <tr>
                <td>IR retenu à la source</td>
                <td class="muted">Barème progressif</td>
                <td class="right red"><?php echo e(number_format($summary['total_ir'], 0, ',', ' ')); ?></td>
            </tr>
            <tr class="total-row">
                <td colspan="2"><strong>Coût employeur total</strong></td>
                <td class="right green"><?php echo e(number_format($summary['total_employer_cost'] ?? 0, 0, ',', ' ')); ?></td>
            </tr>
        </tbody>
    </table>
</div>
<?php endif; ?>


<div class="footer">
    <div class="footer-left">
        <strong><?php echo e($tenant?->name ?? 'Entreprise'); ?></strong><br>
        Document confidentiel — Usage interne uniquement
    </div>
    <div class="footer-right">
        Rapport de paie · <?php echo e($periodLabel); ?><br>
        Généré le <?php echo e(now()->format('d/m/Y à H:i')); ?>

    </div>
</div>
<div class="footer-legal">
    Ce document est généré automatiquement par le système de gestion RH.
    Les montants sont exprimés en MAD sauf indication contraire.
</div>

</body>
</html>
<?php /**PATH D:\Projects\SIRH-\resources\views/salary/export_pdf.blade.php ENDPATH**/ ?>